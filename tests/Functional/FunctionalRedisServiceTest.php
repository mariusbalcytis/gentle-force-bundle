<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalRedisServiceTest extends FunctionalThrottlerTestCase
{
    public function testConnection()
    {
        $container = $this->setUpContainer('redis_service');
        $options = $container->get('maba_gentle_force.redis_client')->getOptions();
        $this->assertSame('custom_service', $options->prefix->getPrefix());
    }
}
