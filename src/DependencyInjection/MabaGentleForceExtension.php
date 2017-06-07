<?php

namespace Maba\Bundle\GentleForceBundle\DependencyInjection;

use Maba\GentleForce\RateLimit\UsageRateLimit;
use Maba\GentleForce\RateLimitProvider;
use Predis\Client;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class MabaGentleForceExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('maba_gentle_force.redis_prefix', $config['redis']['prefix']);

        $redisClientDefinition = new Definition(Client::class, [['host' => $config['redis']['host']]]);
        $container->setDefinition('maba_gentle_force.redis_client', $redisClientDefinition);

        $container->setDefinition(
            'maba_gentle_force.rate_limit_provider',
            $this->buildRateLimitProviderDefinition($config['limits'])
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
}
