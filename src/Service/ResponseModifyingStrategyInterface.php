<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use Maba\GentleForce\IncreaseResult;
use Symfony\Component\HttpFoundation\Response;

/**
 * @api
 */
interface ResponseModifyingStrategyInterface extends StrategyInterface
{
    /**
     * @api
     */
    public function modifyResponse(IncreaseResult $increaseResult, Response $response);
}
