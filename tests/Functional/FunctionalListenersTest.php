<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalListenersTest extends FunctionalRequestTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpContainer('listeners');
    }

    public function testListeners()
    {
        $this->assertUsagesValid(self::PATH_API1, 2);
        $this->assertRequestValid(self::PATH_DOCS);

        $this->sleepUpTo(150);

        $this->assertUsagesValid(self::PATH_API1,  1);
    }
}
