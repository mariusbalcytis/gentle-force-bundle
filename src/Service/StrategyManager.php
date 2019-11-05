<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use InvalidArgumentException;
use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Listener\ListenerConfiguration;
use Maba\GentleForce\IncreaseResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class StrategyManager
{
    private $container;
    private $strategies;

    public function __construct(ContainerInterface $container, array $strategies)
    {
        $this->container = $container;
        $this->strategies = $strategies;
    }

    /**
     * @return Response|null
     */
    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        $configurations = $result->getViolatedConfigurations();
        $lastViolatedConfiguration = end($configurations);

        $strategy = $this->getStrategyForConfiguration($lastViolatedConfiguration);

        return $strategy->getRateLimitExceededResponse($result);
    }

    public function modifyResponse(
        ListenerConfiguration $configuration,
        IncreaseResult $increaseResult,
        Response $response
    ) {
        $strategy = $this->getStrategyForConfiguration($configuration);
        if (!$strategy instanceof ResponseModifyingStrategyInterface) {
            return null;
        }

        $strategy->modifyResponse($increaseResult, $response);
    }

    /**
     * @return StrategyInterface
     */
    private function getStrategyForConfiguration(ListenerConfiguration $configuration)
    {
        $strategyId = $configuration->getStrategyId();

        if (!\in_array($strategyId, $this->strategies, true)) {
            throw new InvalidArgumentException(sprintf('Given strategy (%s) was not registered with strategy manager', $strategyId));
        }

        /** @var StrategyInterface $strategy */
        $strategy = $this->container->get($strategyId);

        return $strategy;
    }
}
