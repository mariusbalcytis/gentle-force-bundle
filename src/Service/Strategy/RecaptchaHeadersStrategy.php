<?php

namespace Maba\Bundle\GentleForceBundle\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\ResponseModifyingStrategyInterface;
use Maba\GentleForce\IncreaseResult;
use Symfony\Component\HttpFoundation\Response;

class RecaptchaHeadersStrategy implements ResponseModifyingStrategyInterface
{
    private $internalStrategy;
    private $headerName;
    private $siteKey;

    /**
     * @param ResponseModifyingStrategyInterface $internalStrategy
     * @param string $headerName
     * @param string $siteKey
     */
    public function __construct(
        ResponseModifyingStrategyInterface $internalStrategy,
        $headerName,
        $siteKey
    ) {
        $this->internalStrategy = $internalStrategy;
        $this->headerName = $headerName;
        $this->siteKey = $siteKey;
    }

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        $response = $this->internalStrategy->getRateLimitExceededResponse($result);

        if ($response === null) {
            return null;
        }

        $response->headers->set($this->headerName, $this->siteKey, true);

        return $response;
    }

    public function modifyResponse(IncreaseResult $increaseResult, Response $response)
    {
        $this->internalStrategy->modifyResponse($increaseResult, $response);
    }
}
