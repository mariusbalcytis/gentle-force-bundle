<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Predis\Connection\ConnectionException;
use Psr\Log\LogLevel;
use Symfony\Component\ErrorHandler\BufferingLogger;

class FunctionalRedisFailureStrategyTest extends FunctionalThrottlerTestCase
{
    public function testFailStrategy()
    {
        $container = $this->setUpContainer('redis_failure_strategy_fail', 'common_basic.yml');
        $throttler = $container->get('maba_gentle_force.throttler');
        $this->expectException(ConnectionException::class);
        $throttler->checkAndIncrease('limit', 'id');
    }

    public function testIgnoreStrategy()
    {
        $container = $this->setUpContainer('redis_failure_strategy_ignore', 'common_basic.yml');
        $throttler = $container->get('maba_gentle_force.throttler');
        $throttler->checkAndIncrease('limit', 'id')->decrease();

        /** @var BufferingLogger $logger */
        $logger = $container->get('logger');
        $this->assertNotEmpty(array_filter($logger->cleanLogs(), function ($logInfo) {
            return $logInfo[0] === LogLevel::ERROR;
        }));
    }
}
