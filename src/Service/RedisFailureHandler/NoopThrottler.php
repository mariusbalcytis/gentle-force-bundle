<?php

namespace Maba\Bundle\GentleForceBundle\Service\RedisFailureHandler;

use Maba\GentleForce\ThrottlerInterface;
use RuntimeException;

class NoopThrottler implements ThrottlerInterface
{
    public function checkAndIncrease($useCaseKey, $identifier)
    {
        throw new RuntimeException('Method not available');
    }

    public function decrease($useCaseKey, $identifier)
    {
        // intentionally empty
    }

    public function reset($useCaseKey, $identifier)
    {
        throw new RuntimeException('Method not available');
    }
}
