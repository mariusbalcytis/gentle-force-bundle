<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class FunctionalInvalidConfigurationTest extends FunctionalRequestTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testInvalidLimitsKey()
    {
        $this->setExpectedException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/limits_key');
    }

    public function testInvalidDefaultStrategyDueToNoSuchService()
    {
        $this->setExpectedException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/default_strategy.no_service');
    }

    public function testInvalidStrategyDueToNoSuchService()
    {
        $this->setExpectedException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/strategy.no_service');
    }

    public function testInvalidStrategyDueToPrivateService()
    {
        $this->setExpectedException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/strategy.private_service');
    }
}
