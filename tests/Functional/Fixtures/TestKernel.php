<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures;

use Maba\Bundle\GentleForceBundle\MabaGentleForceBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class TestKernel extends Kernel
{
    private $configFile;

    public function __construct($testCase)
    {
        parent::__construct('test', true);
        $this->configFile = 'config_' . $testCase . '.yml';
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new MabaGentleForceBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/common.yml');
        $loader->load(__DIR__ . '/config/' . $this->configFile);
    }

    protected function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->setParameter('microtime', microtime());
        $container->setParameter(
            'redis_host',
            isset($_ENV['REDIS_HOST']) ? $_ENV['REDIS_HOST'] : 'localhost'
        );
    }
}
