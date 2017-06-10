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

    public function __construct(Throttler $throttler, RequestIdentifierProvider $requestIdentifierProvider)
    {
        $this->throttler = $throttler;
        $this->requestIdentifierProvider = $requestIdentifierProvider;
    }

    public function addConfiguration(ListenerConfiguration $configuration)
    {
        $this->configurationList[] = $configuration;
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $path = $request->getRequestUri();
        $identifier = $this->requestIdentifierProvider->getIdentifier($request);

        $waitForInSeconds = null;

        foreach ($this->configurationList as $configuration) {
            if (preg_match($configuration->getPathPattern(), $path) === 1) {
                try {
                    $this->throttler->checkAndIncrease($configuration->getLimitsKey(), $identifier);
                } catch (RateLimitReachedException $exception) {
                    if ($waitForInSeconds === null || $waitForInSeconds < $exception->getWaitForInSeconds()) {
                        $waitForInSeconds = $exception->getWaitForInSeconds();
                    }
                }
            }
        }

        if ($waitForInSeconds !== null) {
            $event->setResponse(new Response('', Response::HTTP_TOO_MANY_REQUESTS, [
                'Wait-For' => $waitForInSeconds,
            ]));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }
}
