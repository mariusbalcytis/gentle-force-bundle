<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\Service\RequestIdentifierProvider;
use Maba\Bundle\GentleForceBundle\Service\StrategyManager;
use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\ThrottlerInterface;
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
     * @var array|ListenerConfiguration[]
     */
    private $configurationList = [];

    /**
     * @var SplObjectStorage|CompositeIncreaseResult[]
     */
    private $requestResults;

    private $throttler;
    private $requestIdentifierProvider;
    private $requestMatcher;
    private $strategyManager;

    public function __construct(
        ThrottlerInterface $throttler,
        RequestIdentifierProvider $requestIdentifierProvider,
        RequestMatcher $requestMatcher,
        StrategyManager $strategyManager
    ) {
        $this->throttler = $throttler;
        $this->requestIdentifierProvider = $requestIdentifierProvider;
        $this->requestMatcher = $requestMatcher;
        $this->strategyManager = $strategyManager;

        $this->requestResults = new SplObjectStorage();
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

            $response = $this->strategyManager->getRateLimitExceededResponse($compositeResult);
            if ($response !== null) {
                $event->setResponse($response);
            }
            return;
        }

        $this->requestResults[$request] = $compositeResult;
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
                $this->throttler->checkAndIncrease($configuration->getLimitsKey(), $identifier),
                $configuration
            );
        } catch (RateLimitReachedException $exception) {
            $compositeResult->handleRateLimitReachedException($exception, $configuration);
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
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::RESPONSE => 'onResponse',
            KernelEvents::FINISH_REQUEST => 'onRequestFinished',
        ];
    }
}
