<?php

namespace Maba\Bundle\GentleForceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('maba_gentle_force');

        $children = $rootNode->children();

        $this->configureRedis($children->arrayNode('redis'));
        $this->configureLimits($children->arrayNode('limits'));
        $this->configureStrategies($children->arrayNode('strategies'));
        $this->configureListeners($children->arrayNode('listeners'));
        $this->configureRecaptcha($children->arrayNode('recaptcha'));

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
        $limitChildren->scalarNode('period')->isRequired()->validate()->always(function($value) {
            return $this->parsePeriod($value);
        });
        $limitChildren->scalarNode('bucketed_usages');
        $limitChildren->scalarNode('bucketed_period')->validate()->always(function($value) {
            return $this->parsePeriod($value);
        });
    }

    private function configureStrategies(ArrayNodeDefinition $node)
    {
        $node->addDefaultsIfNotSet();
        $builder = $node->children();
        $builder->scalarNode('default')->defaultValue('headers');

        $headersNode = $builder->arrayNode('headers')->addDefaultsIfNotSet()->children();
        $headersNode->scalarNode('wait_for_header')->defaultNull();
        $headersNode->scalarNode('requests_available_header')->defaultNull();
        $headersNode->scalarNode('content')->defaultValue('Too many requests');
        $headersNode->scalarNode('content_type')->defaultValue('text/plain; charset=UTF-8');

        $logOnlyNode = $builder->arrayNode('log')->children();
        $logOnlyNode->scalarNode('level')->defaultValue('error');

        $recaptchaHeadersNode = $builder->arrayNode('recaptcha_headers')->children();
        $recaptchaHeadersNode->scalarNode('site_key_header')->defaultNull();

        $recaptchaTemplateNode = $builder->arrayNode('recaptcha_template')->children();
        $recaptchaTemplateNode->scalarNode('template')->defaultNull();
    }

    private function configureListeners(ArrayNodeDefinition $node)
    {
        /** @var ArrayNodeDefinition $listenerPrototype */
        $listenerPrototype = $node->prototype('array');
        $listenerChildren = $listenerPrototype->children();
        $listenerChildren->scalarNode('path')->defaultValue('^/');
        $listenerChildren->scalarNode('limits_key')->isRequired();
        $listenerChildren->arrayNode('identifiers')
            ->isRequired()
            ->cannotBeEmpty()
            ->prototype('scalar')
        ;
        $listenerChildren->scalarNode('strategy');
        $listenerChildren->scalarNode('success_matcher');

        $this->buildStatusesNode($listenerChildren, 'success_statuses');
        $this->buildStatusesNode($listenerChildren, 'failure_statuses');

        $methodsNode = $listenerChildren->arrayNode('methods');
        $methodsNode->prototype('scalar');
        $methodsNode->validate()->always(function ($list) {
            return array_map('strtoupper', $list);
        });

        $hostsNode = $listenerChildren->arrayNode('hosts');
        $hostsNode->prototype('scalar');
        $hostsNode->validate()->always(function ($list) {
            return array_map('strtolower', $list);
        });

        $this->addSuccessMatcherValidation($listenerPrototype);
    }

    private function buildStatusesNode(NodeBuilder $node, $name)
    {
        $statusesNode = $node->arrayNode($name);
        $statusesNode->prototype('scalar');
        $statusesNode->validate()->always(function (array $list) {
            return array_map(function ($statusCode) {
                $validatedStatusCode = filter_var($statusCode, FILTER_VALIDATE_INT, ['options' => [
                    'min_range' => 100,
                    'max_range' => 599,
                ]]);
                if ($validatedStatusCode === false) {
                    throw new InvalidConfigurationException(
                        sprintf('Status code %s is invalid', $statusCode)
                    );
                }
                return $validatedStatusCode;
            }, $list);
        });
    }

    private function addSuccessMatcherValidation(ArrayNodeDefinition $node)
    {
        $node->validate()->ifTrue(function (array $configuration) {
            $count = 0;
            if (isset($configuration['success_matcher'])) {
                $count++;
            }
            if (count($configuration['success_statuses']) > 0) {
                $count++;
            }
            if (count($configuration['failure_statuses']) > 0) {
                $count++;
            }
            return $count > 1;
        })->thenInvalid('Only one of success_matcher, success_statuses and failure_statuses can be specified');
    }

    private function configureRecaptcha(ArrayNodeDefinition $node)
    {
        $builder = $node->children();

        $builder->scalarNode('site_key')->isRequired();
        $builder->scalarNode('secret')->isRequired();
    }

    private function parsePeriod($value)
    {
        if ($value === null) {
            return null;
        }

        if (preg_match('/^(\d+(\.\d+)?)([smhdw]?)$/', $value, $matches) !== 1) {
            throw new InvalidConfigurationException(sprintf('Invalid period provided: %s', $value));
        }

        static $periodMap = [
            '' => 1,
            's' => 1,
            'm' => 60,
            'h' => 3600,
            'd' => 86400,
            'w' => 604800,
        ];

        $period = $matches[1] * $periodMap[$matches[3]];

        if ($period <= 0) {
            throw new InvalidConfigurationException(sprintf('Period must be positive: %s', $value));
        }

        return $period;
    }
}
