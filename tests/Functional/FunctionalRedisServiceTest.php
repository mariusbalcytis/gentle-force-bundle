<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\Stopwatch\Stopwatch;

class FunctionalRedisServiceTest extends FunctionalThrottlerTestCase
{
    protected function setUpThrottler($useCaseKey)
    {
        $container = $this->setUpContainer('redis_service');
        $this->throttler = $container->get('maba_gentle_force.throttler');
        $this->useCaseKey = $useCaseKey;
        $this->event = (new Stopwatch())->start('');
    }

    public function testConnection()
    {
        $this->setUpThrottler('use_case');

        $this->assertUsagesValid(1);
    }
}
