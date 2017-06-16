<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Symfony\Component\HttpFoundation\Response;

interface StrategyInterface
{

    /**
     * @param CompositeIncreaseResult $result
     * @return Response
     */
    public function getRateLimitExceededResponse(CompositeIncreaseResult $result);
}
