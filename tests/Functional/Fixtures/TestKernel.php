<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures;

use Maba\Bundle\GentleForceBundle\MabaGentleForceBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    private $configFile;
    private $commonFile;

    public function __construct($testCase, $commonFile = 'common.yml')
    {
        parent::__construct(crc32($testCase . $commonFile), true);
        $this->configFile = $testCase . '.yml';
        $this->commonFile = $commonFile;
    }
    

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            new MabaGentleForceBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/' . $this->commonFile);
        $loader->load(__DIR__ . '/config/' . $this->configFile);
    }
}
