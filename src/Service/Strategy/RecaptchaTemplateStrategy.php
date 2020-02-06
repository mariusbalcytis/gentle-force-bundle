<?php

namespace Maba\Bundle\GentleForceBundle\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\StrategyInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class RecaptchaTemplateStrategy implements StrategyInterface
{
    private $templating;
    private $requestStack;
    private $urlGenerator;
    private $googleRecaptchaSiteKey;
    private $template;

    /**
     * @param string $googleRecaptchaSiteKey
     * @param string $template
     */
    public function __construct(
        Environment $templating,
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

        return
        new Response(
          $this->templating->render($this->template, [
              'siteKey' => $this->googleRecaptchaSiteKey,
              'safeToRefresh' => $safeToRefresh,
              'unlockUrl' => $this->urlGenerator->generate('maba_gentle_force_unlock_recaptcha'),
          ]), Response::HTTP_TOO_MANY_REQUESTS)
        ;
    }
}
