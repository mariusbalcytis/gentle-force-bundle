<?php

namespace Maba\Bundle\GentleForceBundle\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\StrategyInterface;
use Symfony\Component\HttpFoundation\Response;

class HeadersStrategy implements StrategyInterface
{

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        return new Response('', Response::HTTP_TOO_MANY_REQUESTS, [
            'Wait-For' => $result->getWaitForInSeconds(),
        ]);
    }
}
