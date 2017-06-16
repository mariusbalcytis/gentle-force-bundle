<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\IncreaseResult;

class CompositeIncreaseResult
{
    /**
     * @var array|IncreaseResult[]
     */
    private $results = [];

    /**
     * @var float|null
     */
    private $waitForInSeconds;

    private $violatedConfigurations = [];

    public function handleRateLimitReachedException(
        RateLimitReachedException $exception,
        ListenerConfiguration $configuration
    ) {
        if ($this->waitForInSeconds === null || $this->waitForInSeconds < $exception->getWaitForInSeconds()) {
            $this->waitForInSeconds = $exception->getWaitForInSeconds();
        }
        $this->violatedConfigurations[] = $configuration;
    }

    public function addResult(IncreaseResult $result)
    {
        $this->results[] = $result;
    }

    public function isRateLimitReached()
    {
        return $this->waitForInSeconds !== null;
    }

    public function decreaseSuccessfulLimits()
    {
        foreach ($this->results as $result) {
            $result->decrease();
        }
    }

    public function getWaitForInSeconds()
    {
        return $this->waitForInSeconds;
    }

    /**
     * @return array|ListenerConfiguration[]
     */
    public function getViolatedConfigurations()
    {
        return $this->violatedConfigurations;
    }
}
