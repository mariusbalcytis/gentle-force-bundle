<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalIdentifiersTest extends FunctionalRequestTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpContainer('identifiers');
    }

    public function testIdentifiersWithIp()
    {
        $this->assertRequestValid(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestValid(self::PATH_API1, self::DEFAULT_IP);

        $this->assertRequestValid(self::PATH_API1, self::ANOTHER_IP);
        $this->assertRequestValid(self::PATH_DOCS, self::DEFAULT_IP);
        $this->assertRequestInvalid(self::PATH_API1, self::DEFAULT_IP);

        $this->sleepUpTo(150);

        $this->assertRequestValid(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestInvalid(self::PATH_API1, self::DEFAULT_IP);
    }

    public function testIdentifiersWithUsername()
    {
        $this->assertRequestValid(self::PATH_API2, self::DEFAULT_IP, self::DEFAULT_USERNAME);
        $this->assertRequestValid(self::PATH_API2, self::DEFAULT_IP, self::DEFAULT_USERNAME);

        $this->assertRequestValid(self::PATH_API2, self::DEFAULT_IP, self::ANOTHER_USERNAME);
        $this->assertRequestValid(self::PATH_DOCS, self::DEFAULT_IP, self::DEFAULT_USERNAME);
        $this->assertRequestInvalid(self::PATH_API2, self::DEFAULT_IP, self::DEFAULT_USERNAME);

        $this->sleepUpTo(150);

        $this->assertRequestValid(self::PATH_API2, self::DEFAULT_IP, self::DEFAULT_USERNAME);
        $this->assertRequestInvalid(self::PATH_API2, self::DEFAULT_IP, self::DEFAULT_USERNAME);
    }

    public function testSeveralIdentifiers()
    {
        $this->assertRequestValid(self::PATH_API3, self::DEFAULT_IP, self::DEFAULT_USERNAME);
        $this->assertRequestValid(self::PATH_API3, self::DEFAULT_IP, self::DEFAULT_USERNAME);

        $this->assertRequestValid(self::PATH_API3, self::ANOTHER_IP, self::DEFAULT_USERNAME);
        $this->assertRequestValid(self::PATH_API3, self::DEFAULT_IP, self::ANOTHER_USERNAME);
        $this->assertRequestValid(self::PATH_API3, self::DEFAULT_IP);
        $this->assertRequestValid(self::PATH_API3, null, self::DEFAULT_USERNAME);
        $this->assertRequestValid(self::PATH_DOCS, self::DEFAULT_IP, self::DEFAULT_USERNAME);

        $this->assertRequestInvalid(self::PATH_API3, self::DEFAULT_IP, self::DEFAULT_USERNAME);

        $this->sleepUpTo(150);

        $this->assertRequestValid(self::PATH_API3, self::DEFAULT_IP, self::DEFAULT_USERNAME);
        $this->assertRequestInvalid(self::PATH_API3, self::DEFAULT_IP, self::DEFAULT_USERNAME);
    }

    /**
     * Both IP and username are set as identifiers. No user - no blocking.
     */
    public function testBlocksOnlyIfAllIdentifiersAvailable()
    {
        $this->assertRequestValid(self::PATH_API3, self::DEFAULT_IP);
        $this->assertRequestValid(self::PATH_API3, self::DEFAULT_IP);
        $this->assertRequestValid(self::PATH_API3, self::DEFAULT_IP);
    }
}
