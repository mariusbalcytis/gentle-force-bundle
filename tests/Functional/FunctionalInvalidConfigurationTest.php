<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class FunctionalInvalidConfigurationTest extends FunctionalRequestTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testInvalidLimitsKey()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/limits_key');
    }

    public function testInvalidDefaultStrategyDueToNoSuchService()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/default_strategy.no_service');
    }

    public function testInvalidStrategyDueToNoSuchService()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/strategy.no_service');
    }

    public function testInvalidStrategyDueToPrivateService()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/strategy.private_service');
    }

    public function testIncompleteRecaptchaConfiguration()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->setUpContainer('invalid/recaptcha_incomplete');
    }
}
