<?php

namespace Maba\Bundle\GentleForceBundle\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\ResponseModifyingStrategyInterface;
use Maba\GentleForce\IncreaseResult;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class RecaptchaHeadersStrategy implements ResponseModifyingStrategyInterface
{
    private $internalStrategy;
    private $headerName;
    private $siteKey;
    private $urlGenerator;
    private $unlockUrlHeaderName;

    /**
     * @param ResponseModifyingStrategyInterface $internalStrategy
     * @param string $headerName
     * @param string $siteKey
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $unlockUrlHeaderName
     */
    public function __construct(
        ResponseModifyingStrategyInterface $internalStrategy,
        $headerName,
        $siteKey,
        UrlGeneratorInterface $urlGenerator,
        $unlockUrlHeaderName
    ) {
        $this->internalStrategy = $internalStrategy;
        $this->headerName = $headerName;
        $this->siteKey = $siteKey;
        $this->urlGenerator = $urlGenerator;
        $this->unlockUrlHeaderName = $unlockUrlHeaderName;
    }

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        $response = $this->internalStrategy->getRateLimitExceededResponse($result);

        if ($response === null) {
            return null;
        }

        if ($this->unlockUrlHeaderName !== null) {
            $route = $this->urlGenerator->generate(
                'maba_gentle_force_unlock_recaptcha',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $response->headers->set(
                $this->unlockUrlHeaderName,
                $route,
                true
            );
        }

        if ($this->headerName !== null) {
            $response->headers->set($this->headerName, $this->siteKey, true);
        }

        return $response;
    }

    public function modifyResponse(IncreaseResult $increaseResult, Response $response)
    {
        $this->internalStrategy->modifyResponse($increaseResult, $response);
    }
}
