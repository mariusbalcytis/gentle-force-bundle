<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\StrategyInterface;
use Symfony\Component\HttpFoundation\Response;

class CustomStrategy implements StrategyInterface
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        return new Response($this->text);
    }
}
