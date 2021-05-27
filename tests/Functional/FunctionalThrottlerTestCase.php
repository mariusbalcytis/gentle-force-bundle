<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\Throttler;

abstract class FunctionalThrottlerTestCase extends FunctionalTestCase
{
    public const ID = 'user1';
    public const ANOTHER_ID = 'user2';

    /**
     * @var string
     */
    protected $useCaseKey = 'use_case_key';

    /**
     * @var Throttler
     */
    protected $throttler;

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
}
