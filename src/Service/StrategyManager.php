<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use InvalidArgumentException;
use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StrategyManager
{
    private $container;
    private $strategies;

    public function __construct(ContainerInterface $container, array $strategies)
    {
        $this->container = $container;
        $this->strategies = $strategies;
    }

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        $configurations = $result->getViolatedConfigurations();
        $lastViolatedConfiguration = end($configurations);
        $strategyId = $lastViolatedConfiguration->getStrategyId();

        if (!in_array($strategyId, $this->strategies, true)) {
            throw new InvalidArgumentException(sprintf(
                'Given strategy (%s) was not registered with strategy manager',
                $strategyId
            ));
        }

        /** @var StrategyInterface $strategy */
        $strategy = $this->container->get($strategyId);

        return $strategy->getRateLimitExceededResponse($result);
    }
}
