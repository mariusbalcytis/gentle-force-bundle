<?php

namespace Maba\Bundle\GentleForceBundle\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\StrategyInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecaptchaTemplateStrategy implements StrategyInterface
{
    private $templating;
    private $requestStack;
    private $urlGenerator;
    private $googleRecaptchaSiteKey;
    private $template;

    /**
     * @param EngineInterface $templating
     * @param RequestStack $requestStack
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $googleRecaptchaSiteKey
     * @param string $template
     */
    public function __construct(
        EngineInterface $templating,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        $googleRecaptchaSiteKey,
        $template
    ) {
        $this->templating = $templating;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->googleRecaptchaSiteKey = $googleRecaptchaSiteKey;
        $this->template = $template;
    }

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        $request = $this->requestStack->getCurrentRequest();
        $safeToRefresh = $request === null || $request->getMethod() === 'GET';

        return $this->templating->renderResponse($this->template, [
            'siteKey' => $this->googleRecaptchaSiteKey,
            'safeToRefresh' => $safeToRefresh,
            'unlockUrl' => $this->urlGenerator->generate('maba_gentle_force_unlock_recaptcha'),
        ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
    }
}
