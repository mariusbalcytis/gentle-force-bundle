<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\BufferingLogger;

class FunctionalLoggerConfigurationTest extends FunctionalThrottlerTestCase
{
    public function testWithNoLogger()
    {
        $container = $this->setUpContainer('logger', 'common_basic_no_logger.yml');
        $logger = $container->get('logger_wrapper')->logger;
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testWithLogger()
    {
        $container = $this->setUpContainer('logger', 'common_basic.yml');
        $logger = $container->get('logger_wrapper')->logger;
        $this->assertInstanceOf(BufferingLogger::class, $logger);
    }

    public function testWithLoggerAlias()
    {
        $container = $this->setUpContainer('logger', 'common_basic_logger_alias.yml');
        $logger = $container->get('logger_wrapper')->logger;
        $this->assertInstanceOf(BufferingLogger::class, $logger);
    }
}
