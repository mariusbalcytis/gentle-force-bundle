<?php

namespace Maba\Bundle\GentleForceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('maba_gentle_force');

        $children = $rootNode->children();

        $this->configureRedis($children->arrayNode('redis'));
        $this->configureLimits($children->arrayNode('limits'));
        $this->configureListeners($children->arrayNode('listeners'));

        return $treeBuilder;
    }

    private function configureRedis(ArrayNodeDefinition $node)
    {
        $builder = $node->children();
        $builder->scalarNode('host');
        $builder->scalarNode('service_id');
        $builder->scalarNode('prefix')->defaultNull();

        $node->validate()->ifTrue(function ($nodeConfig) {
            return isset($nodeConfig['host']) && isset($nodeConfig['service_id']);
        })->thenInvalid('Only one of host and service_id must be provided');
    }

    private function configureLimits(ArrayNodeDefinition $node)
    {
        /** @var ArrayNodeDefinition $limitsPrototype */
        $limitsPrototype = $node->useAttributeAsKey('name')->prototype('array');

        /** @var ArrayNodeDefinition $limitPrototype */
        $limitPrototype = $limitsPrototype->prototype('array');
        $limitPrototype->validate()->ifTrue(function ($nodeConfig) {
            return isset($nodeConfig['bucketed_usages']) && isset($nodeConfig['bucketed_period']);
        })->thenInvalid('Only one of bucketed_usages and bucketed_period must be provided');

        $limitChildren = $limitPrototype->children();
        $limitChildren->scalarNode('max_usages')->isRequired();
        $limitChildren->scalarNode('period')->isRequired();
        $limitChildren->scalarNode('bucketed_usages');
        $limitChildren->scalarNode('bucketed_period');
    }

    private function configureListeners(ArrayNodeDefinition $node)
    {
        /** @var ArrayNodeDefinition $listenerPrototype */
        $listenerPrototype = $node->prototype('array');
        $listenerChildren = $listenerPrototype->children();
        $listenerChildren->scalarNode('path')->isRequired();
        $listenerChildren->scalarNode('limits_key')->isRequired();
    }
}
