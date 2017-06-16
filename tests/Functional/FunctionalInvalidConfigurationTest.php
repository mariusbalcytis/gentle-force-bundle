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
        $this->setUpContainer('invalid_limits_key');
    }

    public function testInvalidDefaultStrategyService()
    {
        $this->setExpectedException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid_default_strategy_service');
    }

    public function testInvalidStrategyService()
    {
        $this->setExpectedException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid_strategy_service');
    }

    public function testInvalidStrategyServicePrivate()
    {
        $this->setExpectedException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid_strategy_service_private');
    }
}
