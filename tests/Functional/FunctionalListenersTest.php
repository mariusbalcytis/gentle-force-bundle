<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalListenersTest extends FunctionalRequestTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpContainer('listeners');
    }

    public function testListeners()
    {
        $this->assertUsagesValid(self::PATH_API1, 2);
        $this->assertRequestValid(self::PATH_DOCS);

        $this->sleepUpTo(300);

        $this->assertUsagesValid(self::PATH_API1, 1);

        $this->sleepUpTo(600);

        $this->assertUsagesValid(self::PATH_API1, 0);
    }
}
