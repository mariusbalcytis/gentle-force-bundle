<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\IdentifierPriority;
use Maba\Bundle\GentleForceBundle\Service\StrategyManager;
use SplObjectStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var SplObjectStorage|CompositeIncreaseResult[]
     */
    private $requestResults;

    private $configurationManager;
    private $strategyManager;

    public function __construct(
        ConfigurationManager $configurationManager,
        StrategyManager $strategyManager
    ) {
        $this->requestResults = new SplObjectStorage();

        $this->configurationManager = $configurationManager;
        $this->strategyManager = $strategyManager;
    }

    public function onRequest(GetResponseEvent $event)
    {
        $this->handleRequest($event);
    }

    public function onRequestPostAuthentication(GetResponseEvent $event)
    {
        $this->handleRequest($event, IdentifierPriority::PRIORITY_AFTER_AUTHORIZATION);
    }

    private function handleRequest(GetResponseEvent $event, $priority = IdentifierPriority::PRIORITY_NORMAL)
    {
        $request = $event->getRequest();

        $compositeResult = $this->configurationManager->checkAndIncreaseForRequest($request, $priority);

        if ($compositeResult->isRateLimitReached()) {
            $compositeResult->decreaseSuccessfulLimits();

            $response = $this->strategyManager->getRateLimitExceededResponse($compositeResult);
            if ($response !== null) {
                $event->setResponse($response);
            }

            return;
        }

        if (isset($this->requestResults[$request])) {
            $this->requestResults[$request]->mergeFrom($compositeResult);
        } else {
            $this->requestResults[$request] = $compositeResult;
        }
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $compositeResult = $this->getAndRemoveCompositeResult($request);
        if ($compositeResult === null) {
            return;
        }

        $response = $event->getResponse();

        foreach ($compositeResult->getConfigurations() as $configuration) {
            $successMatcher = $configuration->getSuccessMatcher();
            if ($successMatcher !== null && $successMatcher->isResponseSuccessful($response)) {
                $compositeResult->decreaseByConfiguration($configuration);
            }

            $result = $compositeResult->getResultByConfiguration($configuration);
            $this->strategyManager->modifyResponse($configuration, $result, $response);
        }
    }

    public function onRequestFinished(FinishRequestEvent $event)
    {
        unset($this->requestResults[$event->getRequest()]);
    }

    /**
     * @param Request $request
     * @return CompositeIncreaseResult|null
     */
    private function getAndRemoveCompositeResult(Request $request)
    {
        if (!isset($this->requestResults[$request])) {
            return null;
        }

        $compositeResult = $this->requestResults[$request];
        unset($this->requestResults[$request]);

        return $compositeResult;
    }

    public static function getSubscribedEvents()
    {
        // additional listener tags are added in MabaGentleForceExtension class with configurable priorities

        return [
            KernelEvents::RESPONSE => ['onResponse', -1],
            KernelEvents::FINISH_REQUEST => 'onRequestFinished',
        ];
    }
}
