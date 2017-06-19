<?php

namespace Maba\Bundle\GentleForceBundle\DependencyInjection;

use Maba\Bundle\GentleForceBundle\Listener\ListenerConfiguration;
use Maba\Bundle\GentleForceBundle\Service\SuccessMatcher\ResponseCodeSuccessMatcher;
use Maba\GentleForce\RateLimit\UsageRateLimit;
use Maba\GentleForce\RateLimitProvider;
use Predis\Client;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class MabaGentleForceExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('maba_gentle_force.redis_prefix', $config['redis']['prefix']);

        $this->registerRedisClient($container, $config['redis']);

        $container->setDefinition(
            'maba_gentle_force.rate_limit_provider',
            $this->buildRateLimitProviderDefinition($config['limits'])
        );

        $this->registerListeners(
            $container,
            $loader,
            $config['listeners'],
            $config['strategies'],
            array_keys($config['limits'])
        );
    }

    private function buildRateLimitProviderDefinition(array $limitsConfiguration)
    {
        $rateLimitProviderDefinition = new Definition(RateLimitProvider::class);

        foreach ($limitsConfiguration as $useCaseKey => $limitsForUseCase) {
            $rateLimits = array_map([$this, 'buildLimitDefinition'], $limitsForUseCase);
            $rateLimitProviderDefinition->addMethodCall(
                'registerRateLimits',
                [$useCaseKey, $rateLimits]
            );
        }

        return $rateLimitProviderDefinition;
    }

    private function buildLimitDefinition(array $limitConfiguration)
    {
        $limitDefinition = new Definition(UsageRateLimit::class, [
            $limitConfiguration['max_usages'],
            $limitConfiguration['period'],
        ]);

        if (isset($limitConfiguration['bucketed_period'])) {
            $limitDefinition->addMethodCall(
                'setBucketedPeriod',
                [$limitConfiguration['bucketed_period']]
            );
        } elseif (isset($limitConfiguration['bucketed_usages'])) {
            $limitDefinition->addMethodCall(
                'setBucketedUsages',
                [$limitConfiguration['bucketed_usages']]
            );
        }

        return $limitDefinition;
    }

    private function registerRedisClient(ContainerBuilder $container, array $redisConfig)
    {
        if (isset($redisConfig['service_id'])) {
            $container->setAlias('maba_gentle_force.redis_client', $redisConfig['service_id']);
            return;
        }

        $parameters = null;
        if (isset($redisConfig['host'])) {
            $parameters = ['host' => $redisConfig['host']];
        }
        $redisClientDefinition = new Definition(Client::class, [$parameters]);
        $container->setDefinition('maba_gentle_force.redis_client', $redisClientDefinition);
    }

    private function registerListeners(
        ContainerBuilder $container,
        XmlFileLoader $loader,
        array $listenerConfigList,
        array $strategiesConfiguration,
        array $availableLimitsKeys
    ) {
        if (count($listenerConfigList) === 0) {
            return;
        }

        $loader->load('listener.xml');

        $bundlesAvailable = $container->getParameter('kernel.bundles');
        if (isset($bundlesAvailable['SecurityBundle'])) {
            $loader->load('username_provider.xml');
        }

        $usedStrategies = [];
        $defaultStrategyId = $this->getServiceIdForStrategy($strategiesConfiguration['default']);
        $requestListenerDefinition = $container->getDefinition('maba_gentle_force.request_listener');

        foreach ($listenerConfigList as $listenerConfig) {
            $strategyId = isset($listenerConfig['strategy'])
                ? $this->getServiceIdForStrategy($listenerConfig['strategy'])
                : $defaultStrategyId;
            $usedStrategies[] = $strategyId;

            $limitsKey = $listenerConfig['limits_key'];
            if (!in_array($limitsKey, $availableLimitsKeys, true)) {
                throw new InvalidConfigurationException(sprintf(
                    'Specified limits_key (%s) is not registered',
                    $limitsKey
                ));
            }

            $pathPattern = '#' . str_replace('#', '\\#', $listenerConfig['path']) . '#';
            $configurationDefinition = (new Definition(ListenerConfiguration::class))
                ->addMethodCall('setPathPattern', [$pathPattern])
                ->addMethodCall('setLimitsKey', [$limitsKey])
                ->addMethodCall('setIdentifierTypes', [$listenerConfig['identifiers']])
                ->addMethodCall('setHosts', [$listenerConfig['hosts']])
                ->addMethodCall('setMethods', [$listenerConfig['methods']])
                ->addMethodCall('setStrategyId', [$strategyId])
                ->addMethodCall('setSuccessMatcher', [$this->buildSuccessMatcher($listenerConfig)])
            ;
            $requestListenerDefinition->addMethodCall('addConfiguration', [$configurationDefinition]);
        }

        $usedStrategies = array_unique($usedStrategies);
        $container->setParameter('maba_gentle_force.strategy_manager.strategies', $usedStrategies);

        $this->includeStrategyDefinitions($loader, $usedStrategies);
        $this->configureStrategies($container, $strategiesConfiguration);
    }

    private function buildSuccessMatcher(array $listenerConfig)
    {
        if (isset($listenerConfig['success_matcher'])) {
            return new Reference($listenerConfig['success_matcher']);
        }

        if (count($listenerConfig['success_statuses']) > 0) {
            return new Definition(
                ResponseCodeSuccessMatcher::class,
                [$listenerConfig['success_statuses']]
            );
        }

        if (count($listenerConfig['failure_statuses']) > 0) {
            return new Definition(
                ResponseCodeSuccessMatcher::class,
                [$listenerConfig['failure_statuses'], true]
            );
        }

        return null;
    }

    private function getServiceIdForStrategy($strategy)
    {
        $predefinedStrategies = [
            'headers' => 'maba_gentle_force.strategy.headers',
            'log' => 'maba_gentle_force.strategy.log',
        ];
        return isset($predefinedStrategies[$strategy]) ? $predefinedStrategies[$strategy] : $strategy;
    }

    private function includeStrategyDefinitions(XmlFileLoader $loader, array $usedStrategies)
    {
        if (in_array('maba_gentle_force.strategy.log', $usedStrategies, true)) {
            $loader->load('log_strategy.xml');
        }
    }

    private function configureStrategies(ContainerBuilder $container, array $strategiesConfiguration)
    {
        if (isset($strategiesConfiguration['log'])) {
            $container->setParameter(
                'maba_gentle_force.strategy.log.level',
                $strategiesConfiguration['log']['level']
            );
        }
        if (isset($strategiesConfiguration['headers'])) {
            $container->setParameter(
                'maba_gentle_force.strategy.headers.wait_for_header',
                $strategiesConfiguration['headers']['wait_for_header']
            );
            $container->setParameter(
                'maba_gentle_force.strategy.headers.requests_available_header',
                $strategiesConfiguration['headers']['requests_available_header']
            );
        }
    }
}
