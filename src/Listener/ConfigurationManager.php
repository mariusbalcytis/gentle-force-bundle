<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\Service\RequestIdentifierProvider;
use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\ThrottlerInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigurationManager
{
    /**
     * @var array|ListenerConfiguration[]
     */
    private $configurationList = [];

    private $throttler;
    private $requestIdentifierProvider;
    private $requestMatcher;
    private $whitelistedControllers;

    public function __construct(
        ThrottlerInterface $throttler,
        RequestIdentifierProvider $requestIdentifierProvider,
        RequestMatcher $requestMatcher,
        array $whitelistedControllers
    ) {
        $this->throttler = $throttler;
        $this->requestIdentifierProvider = $requestIdentifierProvider;
        $this->requestMatcher = $requestMatcher;
        $this->whitelistedControllers = $whitelistedControllers;
    }

    public function addConfiguration(ListenerConfiguration $configuration)
    {
        $this->configurationList[] = $configuration;
    }

    public function checkAndIncreaseForRequest(Request $request)
    {
        $compositeResult = new CompositeIncreaseResult();

        if ($this->isWhitelisted($request)) {
            return $compositeResult;
        }

        $identifierHelper = new IdentifierHelper($this->requestIdentifierProvider, $request);

        foreach ($this->configurationList as $configuration) {
            if ($this->requestMatcher->matches($configuration, $request)) {
                $this->checkAndIncrease($identifierHelper, $configuration, $compositeResult);
            }
        }

        return $compositeResult;
    }

    private function isWhitelisted($request)
    {
        $controller = $request->attributes->get('_controller');
        return $controller !== null && in_array($controller, $this->whitelistedControllers, true);
    }

    private function checkAndIncrease(
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

    public function resetForStrategies(Request $request, array $strategyList)
    {
        $identifierHelper = new IdentifierHelper($this->requestIdentifierProvider, $request);

        foreach ($this->configurationList as $configuration) {
            if (in_array($configuration->getStrategyId(), $strategyList, true)) {
                $this->reset($identifierHelper, $configuration);
            }
        }
    }

    private function reset(IdentifierHelper $identifierHelper, ListenerConfiguration $configuration)
    {
        $identifier = $identifierHelper->getIdentifier($configuration);
        if ($identifier === null) {
            return;
        }

        $this->throttler->reset($configuration->getLimitsKey(), $identifier);
    }
}
