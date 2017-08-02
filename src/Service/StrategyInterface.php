<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Symfony\Component\HttpFoundation\Response;

/**
 * @api
 */
interface StrategyInterface
{
    /**
     * @param CompositeIncreaseResult $result
     * @return Response|null returns null if request should be proceeded
     *
     * @api
     */
    public function getRateLimitExceededResponse(CompositeIncreaseResult $result);
}
