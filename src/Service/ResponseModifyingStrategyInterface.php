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
     * @param IncreaseResult $increaseResult
     * @param Response $response
     *
     * @api
     */
    public function modifyResponse(IncreaseResult $increaseResult, Response $response);
}
