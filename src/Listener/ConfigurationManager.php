<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\Service\RequestIdentifierProvider;
use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\ThrottlerInterface;
use Psr\Log\LoggerInterface;
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
    private $logger;

    public function __construct(
        ThrottlerInterface $throttler,
        RequestIdentifierProvider $requestIdentifierProvider,
        RequestMatcher $requestMatcher,
        array $whitelistedControllers,
        LoggerInterface $logger
    ) {
        $this->throttler = $throttler;
        $this->requestIdentifierProvider = $requestIdentifierProvider;
        $this->requestMatcher = $requestMatcher;
        $this->whitelistedControllers = $whitelistedControllers;
        $this->logger = $logger;
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
            $this->logger->info('Rate limit exceeded', [
                'limits_key' => $configuration->getLimitsKey(),
                'identifier' => $identifier,
            ]);
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

        $this->logger->info('Rate limits has been reset', [
            'limits_key' => $configuration->getLimitsKey(),
            'identifier' => $identifier,
        ]);
    }
}
