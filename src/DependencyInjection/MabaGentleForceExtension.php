<?php

namespace Maba\Bundle\GentleForceBundle\DependencyInjection;

use Maba\Bundle\GentleForceBundle\Listener\ListenerConfiguration;
use Maba\GentleForce\RateLimit\UsageRateLimit;
use Maba\GentleForce\RateLimitProvider;
use Predis\Client;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
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

        $this->registerListeners($container, $loader, $config['listeners']);
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
        array $listenerConfigList
    ) {
        if (count($listenerConfigList) === 0) {
            return;
        }

        $loader->load('listener.xml');

        $requestListenerDefinition = $container->getDefinition('maba_gentle_force.request_listener');

        foreach ($listenerConfigList as $listenerConfig) {
            $pathPattern = '#' . str_replace('#', '\\#', $listenerConfig['path']) . '#';
            $requestListenerDefinition->addMethodCall('addConfiguration', [
                (new Definition(ListenerConfiguration::class))
                    ->addMethodCall('setPathPattern', [$pathPattern])
                    ->addMethodCall('setLimitsKey', [$listenerConfig['limits_key']]),
            ]);
        }
    }
}
