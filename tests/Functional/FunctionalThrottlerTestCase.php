<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\Throttler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\StopwatchEvent;

abstract class FunctionalThrottlerTestCase extends TestCase
{
    const ID = 'user1';
    const ANOTHER_ID = 'user2';
    const ERROR_CORRECTION_PERIOD_MS = 60;

    /**
     * @var string
     */
    protected $useCaseKey = 'use_case_key';

    /**
     * @var Throttler
     */
    protected $throttler;

    /**
     * @var StopwatchEvent
     */
    protected $event;

    protected function assertUsagesValid($countOfUsages)
    {
        for ($i = 0; $i < $countOfUsages; $i++) {
            $this->checkAndIncrease();
        }

        $this->checkAndIncrease(self::ANOTHER_ID);
        $this->addToAssertionCount(1);  // does not fail if other identifier was passed

        $this->assertUsageInvalid();
    }

    protected function assertUsageInvalid()
    {
        try {
            $this->checkAndIncrease();

            $this->fail('Should have failed, but RateLimitReachedException was not thrown');
        } catch (RateLimitReachedException $exception) {
            $this->addToAssertionCount(1);  // should fail as 0.2 seconds did not yet pass
        }
    }

    protected function checkAndIncrease($id = self::ID)
    {
        return $this->throttler->checkAndIncrease($this->useCaseKey, $id);
    }

    protected function reset($id = self::ID)
    {
        $this->throttler->reset($this->useCaseKey, $id);
    }

    protected function sleepUpTo($milliseconds)
    {
        $duration = $this->event->lap()->getDuration();
        $this->sleepMs($milliseconds - $duration + self::ERROR_CORRECTION_PERIOD_MS);
    }

    protected function sleepMs($milliseconds)
    {
        usleep($milliseconds * 1000);
    }
}
