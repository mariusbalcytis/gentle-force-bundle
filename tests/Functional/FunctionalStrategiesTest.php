<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalStrategiesTest extends FunctionalRequestTestCase
{
    protected function setUp() :void
    {
        parent::setUp();
        $this->setUpContainer('strategies');
    }

    public function testStrategies()
    {
        $this->assertRequestValid(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestValid(self::PATH_API1, self::DEFAULT_IP);

        $this->assertRequestValid(self::PATH_API1, self::ANOTHER_IP);
        $this->assertRequestValid(self::PATH_DOCS, self::DEFAULT_IP);
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertSame(
            'too many requests',
            $response->getContent(),
            'Expected request to be blocked with custom response'
        );

        $this->sleepUpTo(150);

        $this->assertRequestValid(self::PATH_API1, self::DEFAULT_IP);
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertSame(
            'try later',
            $response->getContent(),
            'Expected request to be blocked with custom response'
        );
    }
}
