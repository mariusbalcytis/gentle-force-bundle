<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Service\ResetInterface;

abstract class FunctionalTestCase extends TestCase
{
    public const PATH_DOCS = '/docs/api/main';
    public const PATH_API1 = '/api/resource';
    public const PATH_API1_OK = '/api/ok';
    public const PATH_API1_OK2 = '/api/ok2';
    public const PATH_API2 = '/api2/resource';
    public const PATH_API3 = '/api3/resource';

    /**
     * @var TestKernel
     */
    protected $kernel;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param string $testCase
     * @param string $commonFile
     * @return ContainerInterface
     */
    protected function setUpContainer($testCase, $commonFile = 'common.yml')
    {
        $this->kernel = new TestKernel($testCase, $commonFile);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
        $this->container->get('microtime_provider_mock')->setMockedMicrotime(0);
        return $this->container;
    }

    protected function tearDown(): void
    {
        $this->kernel->shutdown();
        if ($this->container instanceof ResetInterface) {
            $this->container->reset();
        }

        $filesystem = new Filesystem();
        $filesystem->remove($this->kernel->getProjectDir() . '/var/cache');
    }

    protected function sleepUpTo($milliseconds)
    {
        $this->container->get('microtime_provider_mock')
            ->setMockedMicrotime($milliseconds / 1000)
        ;
    }
}
