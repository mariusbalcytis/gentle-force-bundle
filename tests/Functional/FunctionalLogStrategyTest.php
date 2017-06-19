<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\HttpKernel\Tests\Logger;

class FunctionalLogStrategyTest extends FunctionalRequestTestCase
{
    /**
     * @var Logger
     */
    private $logger;

    protected function setUp()
    {
        parent::setUp();
        $this->setUpContainer('log_strategy');
        $this->logger = $this->kernel->getContainer()->get('logger');
    }

    public function testLogStrategy()
    {
        $this->assertRequestValid(self::PATH_API1);
        $this->assertRequestValid(self::PATH_API1);
        $this->assertCount(0, $this->logger->getLogs('warning'));

        $this->assertRequestValid(self::PATH_API1);
        $this->assertCount(1, $this->logger->getLogs('warning'));

        $this->sleepUpTo(500);
        $this->logger->clear();

        $this->assertRequestValid(self::PATH_API1);
        $this->assertCount(0, $this->logger->getLogs('warning'));

        $this->assertRequestValid(self::PATH_API1);
        $this->assertCount(1, $this->logger->getLogs('warning'));
    }
}
