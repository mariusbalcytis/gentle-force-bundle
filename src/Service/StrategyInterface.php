<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Symfony\Component\HttpFoundation\Response;

interface StrategyInterface
{
    /**
     * @param CompositeIncreaseResult $result
     * @return Response|null returns null if request should be proceeded
     */
    public function getRateLimitExceededResponse(CompositeIncreaseResult $result);
}
