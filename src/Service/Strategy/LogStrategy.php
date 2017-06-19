<?php

namespace Maba\Bundle\GentleForceBundle\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\StrategyInterface;
use Psr\Log\LoggerInterface;

class LogStrategy implements StrategyInterface
{
    private $logger;
    private $level;

    /**
     * @param LoggerInterface $logger
     * @param string $level
     */
    public function __construct(LoggerInterface $logger, $level)
    {
        $this->logger = $logger;
        $this->level = $level;
    }

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        $this->logger->log(
            $this->level,
            'Rate limit was exceeded. Proceeding with request as "log" strategy was configured',
            [
                'wait_for' => $result->getWaitForInSeconds(),
                'violated_configurations' => $result->getViolatedConfigurations(),
            ]
        );

        return null;
    }
}
