<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class FunctionalTestCase extends TestCase
{
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
        return $this->kernel->getContainer();
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
