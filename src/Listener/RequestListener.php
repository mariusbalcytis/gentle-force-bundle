<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\Service\RequestIdentifierProvider;
use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\Throttler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var array|ListenerConfiguration[]
     */
    private $configurationList = [];

    private $throttler;
    private $requestIdentifierProvider;
    private $requestMatcher;

    public function __construct(
        Throttler $throttler,
        RequestIdentifierProvider $requestIdentifierProvider,
        RequestMatcher $requestMatcher
    ) {
        $this->throttler = $throttler;
        $this->requestIdentifierProvider = $requestIdentifierProvider;
        $this->requestMatcher = $requestMatcher;
    }

    public function addConfiguration(ListenerConfiguration $configuration)
    {
        $this->configurationList[] = $configuration;
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $identifierHelper = new IdentifierHelper($this->requestIdentifierProvider, $request);
        $compositeResult = new CompositeIncreaseResult();

        foreach ($this->configurationList as $configuration) {
            if ($this->requestMatcher->matches($configuration, $request)) {
                $this->handleConfiguration($identifierHelper, $configuration, $compositeResult);
            }
        }

        if ($compositeResult->isRateLimitReached()) {
            $compositeResult->decreaseSuccessfulLimits();

            $event->setResponse(new Response('', Response::HTTP_TOO_MANY_REQUESTS, [
                'Wait-For' => $compositeResult->getWaitForInSeconds(),
            ]));
        }
    }

    private function handleConfiguration(
        IdentifierHelper $identifierHelper,
        ListenerConfiguration $configuration,
        CompositeIncreaseResult $compositeResult
    ) {
        $identifier = $identifierHelper->getIdentifier($configuration);
        if ($identifier === null) {
            return;
        }

        try {
            $compositeResult->addResult(
                $this->throttler->checkAndIncrease($configuration->getLimitsKey(), $identifier)
            );
        } catch (RateLimitReachedException $exception) {
            $compositeResult->handleRateLimitReachedException($exception);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }
}
