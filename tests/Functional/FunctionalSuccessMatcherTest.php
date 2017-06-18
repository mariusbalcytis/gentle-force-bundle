<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalSuccessMatcherTest extends FunctionalRequestTestCase
{
    public function testSuccessMatcher()
    {
        $this->setUpContainer('success_matcher');
        $this->makeAssertions();
    }

    public function testFailureStatuses()
    {
        $this->setUpContainer('failure_statuses');
        $this->makeAssertions();
    }

    public function testSuccessStatuses()
    {
        $this->setUpContainer('success_statuses');
        $this->makeAssertions(self::PATH_API1_OK2);
    }

    private function makeAssertions($additionalUri = self::PATH_API1_OK)
    {
        $this->assertRequestValid(self::PATH_API1_OK);
        $this->assertRequestValid($additionalUri);
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
