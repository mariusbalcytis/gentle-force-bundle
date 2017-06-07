<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\Stopwatch\Stopwatch;

class FunctionalLimitsTest extends FunctionalThrottlerTestCase
{
    protected function setUpThrottler($useCaseKey)
    {
        $container = $this->setUpContainer('limits');
        $this->throttler = $container->get('maba_gentle_force.throttler');
        $this->useCaseKey = $useCaseKey;
        $this->event = (new Stopwatch())->start('');
    }

    public function testNoBucketed()
    {
        $this->setUpThrottler('2_in_03_no_bucketed');

        $this->assertUsagesValid(2);

        $this->sleepUpTo(150);

        $this->assertUsagesValid(1);
    }

    public function testBucketedPeriod()
    {
        $this->setUpThrottler('2_in_03_bucketed_period_03');

        $this->assertUsagesValid(4);

        $this->sleepUpTo(150);

        $this->assertUsagesValid(1);
    }

    public function testBucketedUsages()
    {
        $this->setUpThrottler('2_in_03_bucketed_usages_1');

        $this->assertUsagesValid(3);

        $this->sleepUpTo(150);

        $this->assertUsagesValid(1);
    }

    public function testSeveralLimits()
    {
        $this->setUpThrottler('2_in_06_and_3_in_3');

        $this->assertUsagesValid(2);

        $this->sleepUpTo(600);

        $this->assertUsagesValid(1);
    }
}
