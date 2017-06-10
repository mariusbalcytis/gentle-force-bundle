<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalRedisServiceTest extends FunctionalThrottlerTestCase
{
    protected function setUpThrottler($useCaseKey)
    {
        $container = $this->setUpContainer('redis_service');
        $this->throttler = $container->get('maba_gentle_force.throttler');
        $this->useCaseKey = $useCaseKey;
    }

    public function testConnection()
    {
        $this->setUpThrottler('use_case');

        $this->assertUsagesValid(1);
    }
}
