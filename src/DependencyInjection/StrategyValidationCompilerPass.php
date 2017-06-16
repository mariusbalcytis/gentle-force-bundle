<?php

namespace Maba\Bundle\GentleForceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StrategyValidationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('maba_gentle_force.strategy_manager')) {
            return;
        }

        $strategyIdList = $container->getParameter('maba_gentle_force.strategy_manager.strategies');

        foreach ($strategyIdList as $strategyId) {
            if (!$container->hasDefinition($strategyId)) {
                throw new InvalidConfigurationException(sprintf(
                    'Gentle force strategy "%s" does not exist in service container',
                    $strategyId
                ));
            }

            $definition = $container->getDefinition($strategyId);
            if (!$definition->isPublic()) {
                throw new InvalidConfigurationException(sprintf(
                    'Service "%s" must be public as this is required for gentle force strategies',
                    $strategyId
                ));
            }
        }
    }
}
