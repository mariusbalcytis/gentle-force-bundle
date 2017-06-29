<?php

namespace Maba\Bundle\GentleForceBundle\Service\RedisFailureHandler;

use Maba\GentleForce\IncreaseResult;
use Maba\GentleForce\RateLimitProvider;
use Maba\GentleForce\ThrottlerInterface;
use Predis\Connection\ConnectionException;
use Psr\Log\LoggerInterface;

class FailureHandlingThrottler implements ThrottlerInterface
{
    private $throttler;
    private $rateLimitProvider;
    private $logger;

    public function __construct(
        ThrottlerInterface $throttler,
        RateLimitProvider $rateLimitProvider,
        LoggerInterface $logger
    ) {
        $this->throttler = $throttler;
        $this->rateLimitProvider = $rateLimitProvider;
        $this->logger = $logger;
    }

    public function checkAndIncrease($useCaseKey, $identifier)
    {
        try {
            return $this->throttler->checkAndIncrease($useCaseKey, $identifier);
        } catch (ConnectionException $exception) {
            $this->logger->error('Connection to redis failed while checking rate limits', [
                'exception' => $exception,
            ]);
        }

        $usagesAvailable = 0;
        foreach ($this->rateLimitProvider->getRateLimits($useCaseKey) as $rateLimit) {
            $usagesAvailable = max($rateLimit->calculateBucketSize(), $usagesAvailable);
        }
        return new IncreaseResult(new NoopThrottler(), $usagesAvailable, $useCaseKey, $identifier);
    }

    public function decrease($useCaseKey, $identifier)
    {
        return $this->throttler->decrease($useCaseKey, $identifier);
    }

    public function reset($useCaseKey, $identifier)
    {
        return $this->throttler->reset($useCaseKey, $identifier);
    }
}
