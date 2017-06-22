<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

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
        $this->configurationManager = $configurationManager;
        $this->strategyManager = $strategyManager;

        $this->requestResults = new SplObjectStorage();
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $compositeResult = $this->configurationManager->checkAndIncreaseForRequest($request);

        if ($compositeResult->isRateLimitReached()) {
            $compositeResult->decreaseSuccessfulLimits();

            $response = $this->strategyManager->getRateLimitExceededResponse($compositeResult);
            if ($response !== null) {
                $event->setResponse($response);
            }
            return;
        }

        $this->requestResults[$request] = $compositeResult;
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
        return [
            KernelEvents::REQUEST => ['onRequest', 1],
            KernelEvents::RESPONSE => ['onResponse', -1],
            KernelEvents::FINISH_REQUEST => 'onRequestFinished',
        ];
    }
}
