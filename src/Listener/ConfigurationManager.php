<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\Service\IdentifierBuilder;
use Maba\Bundle\GentleForceBundle\Service\RequestIdentifierProvider;
use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\ThrottlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigurationManager
{
    private $throttler;
    private $requestIdentifierProvider;
    private $identifierBuilder;
    private $requestMatcher;
    private $whitelistedControllers;
    private $logger;
    private $configurationRegistry;
    private $priorityResolver;

    public function __construct(
        ThrottlerInterface $throttler,
        RequestIdentifierProvider $requestIdentifierProvider,
        IdentifierBuilder $identifierBuilder,
        RequestMatcher $requestMatcher,
        array $whitelistedControllers,
        LoggerInterface $logger,
        ConfigurationRegistry $configurationRegistry,
        PriorityResolver $priorityResolver
    ) {
        $this->throttler = $throttler;
        $this->requestIdentifierProvider = $requestIdentifierProvider;
        $this->identifierBuilder = $identifierBuilder;
        $this->requestMatcher = $requestMatcher;
        $this->whitelistedControllers = $whitelistedControllers;
        $this->logger = $logger;
        $this->configurationRegistry = $configurationRegistry;
        $this->priorityResolver = $priorityResolver;
    }

    public function checkAndIncreaseForRequest(Request $request, $priority)
    {
        $compositeResult = new CompositeIncreaseResult();

        if ($this->isWhitelisted($request)) {
            return $compositeResult;
        }

        $identifierHelper = $this->createIdentifierHelper($request);

        foreach ($this->configurationRegistry->getConfigurationList() as $configuration) {
            if ($this->priorityResolver->resolvePriority($configuration) !== $priority) {
                continue;
            }

            if ($this->requestMatcher->matches($configuration, $request)) {
                $this->checkAndIncrease($identifierHelper, $configuration, $compositeResult);
            }
        }

        return $compositeResult;
    }

    private function isWhitelisted($request)
    {
        $controller = $request->attributes->get('_controller');

        return $controller !== null && \in_array($controller, $this->whitelistedControllers, true);
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
        $identifierHelper = $this->createIdentifierHelper($request);

        foreach ($this->configurationRegistry->getConfigurationList() as $configuration) {
            if (\in_array($configuration->getStrategyId(), $strategyList, true)) {
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

    private function createIdentifierHelper(Request $request)
    {
        return new IdentifierHelper(
            $this->requestIdentifierProvider,
            $this->identifierBuilder,
            $request
        );
    }
}
