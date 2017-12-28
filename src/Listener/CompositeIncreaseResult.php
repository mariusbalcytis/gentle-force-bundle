<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use InvalidArgumentException;
use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\IncreaseResult;
use SplObjectStorage;

class CompositeIncreaseResult
{
    /**
     * @var array|IncreaseResult[]
     */
    private $results = [];

    /**
     * @var array|ListenerConfiguration[]
     */
    private $configurations = [];

    /**
     * @var SplObjectStorage|IncreaseResult[]
     */
    private $resultsByConfiguration;

    /**
     * @var float|null
     */
    private $waitForInSeconds;

    /**
     * @var array|ListenerConfiguration[]
     */
    private $violatedConfigurations = [];

    public function __construct()
    {
        $this->resultsByConfiguration = new SplObjectStorage();
    }

    public function handleRateLimitReachedException(
        RateLimitReachedException $exception,
        ListenerConfiguration $configuration
    ) {
        if ($this->waitForInSeconds === null || $this->waitForInSeconds < $exception->getWaitForInSeconds()) {
            $this->waitForInSeconds = $exception->getWaitForInSeconds();
        }
        $this->violatedConfigurations[] = $configuration;
    }

    public function addResult(IncreaseResult $result, ListenerConfiguration $configuration)
    {
        $this->results[] = $result;
        $this->configurations[] = $configuration;
        $this->resultsByConfiguration[$configuration] = $result;
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

    public function decreaseByConfiguration(ListenerConfiguration $configuration)
    {
        $this->getResultByConfiguration($configuration)->decrease();
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

    /**
     * @return array|ListenerConfiguration[]
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @param ListenerConfiguration $configuration
     * @return IncreaseResult
     */
    public function getResultByConfiguration(ListenerConfiguration $configuration)
    {
        if (!isset($this->resultsByConfiguration[$configuration])) {
            throw new InvalidArgumentException('No result registered by provided configuration');
        }

        return $this->resultsByConfiguration[$configuration];
    }

    public function mergeFrom(self $compositeIncreaseResult)
    {
        $this->results = array_merge($this->results, $compositeIncreaseResult->results);
        $this->configurations = array_merge($this->configurations, $compositeIncreaseResult->configurations);
        $this->resultsByConfiguration->addAll($compositeIncreaseResult->resultsByConfiguration);
        if ($this->waitForInSeconds === null || $this->waitForInSeconds < $compositeIncreaseResult->waitForInSeconds) {
            $this->waitForInSeconds = $compositeIncreaseResult->waitForInSeconds;
        }
        $this->violatedConfigurations = array_merge(
            $this->violatedConfigurations,
            $compositeIncreaseResult->violatedConfigurations
        );
    }
}
