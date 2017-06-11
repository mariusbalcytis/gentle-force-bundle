<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class FunctionalTestCase extends TestCase
{
    use TimeAwareTrait;

    const PATH_DOCS = '/docs/api/main';
    const PATH_API1 = '/api/resource';
    const PATH_API2 = '/api2/resource';
    const PATH_API3 = '/api3/resource';

    /**
     * @var TestKernel
     */
    protected $kernel;

    /**
     * @return ContainerInterface
     * @param mixed $testCase
     */
    protected function setUpContainer($testCase)
    {
        $this->kernel = new TestKernel($testCase);
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        $this->startTimer();
        return $container;
    }

    protected function tearDown()
    {
        $container = $this->kernel->getContainer();
        $this->kernel->shutdown();
        if ($container instanceof ResettableContainerInterface) {
            $container->reset();
        }

        $filesystem = new Filesystem();
        $filesystem->remove($this->kernel->getRootDir() . '/cache');
    }
}
