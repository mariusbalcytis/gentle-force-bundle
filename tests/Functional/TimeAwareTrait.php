<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

trait TimeAwareTrait
{
    /**
     * @var StopwatchEvent
     */
    private $event;

    protected function startTimer()
    {
        $this->event = (new Stopwatch())->start('');
    }

    protected function sleepUpTo($milliseconds)
    {
        $duration = $this->event->lap()->getDuration();
        $this->sleepMs($milliseconds - $duration + $this->getErrorCorrectionPeriodMs());
    }

    private function sleepMs($milliseconds)
    {
        usleep($milliseconds * 1000);
    }

    private function getErrorCorrectionPeriodMs()
    {
        return 100;
    }
}
