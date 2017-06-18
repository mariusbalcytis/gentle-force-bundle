<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalSuccessMatcherTest extends FunctionalRequestTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpContainer('success_matcher');
    }

    public function testSuccessMatcher()
    {
        $this->assertRequestValid(self::PATH_API1_OK);
        $this->assertRequestValid(self::PATH_API1_OK);
        $this->assertRequestValid(self::PATH_API1_OK);

        $this->assertUsagesValid(self::PATH_API1, 2);
        $this->assertRequestInvalid(self::PATH_API1_OK);
        $this->assertRequestValid(self::PATH_DOCS);

        $this->sleepUpTo(500);

        $this->assertRequestValid(self::PATH_API1_OK);
        $this->assertUsagesValid(self::PATH_API1, 1);
        $this->assertRequestInvalid(self::PATH_API1_OK);
    }
}
