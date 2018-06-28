<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures;

class MockRequestListener
{
    private $triggeredCount = 0;

    public function onKernelRequest()
    {
        $this->triggeredCount++;
    }

    public function getTriggeredCount()
    {
        return $this->triggeredCount;
    }
}
