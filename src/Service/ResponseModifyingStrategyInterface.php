<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use Maba\GentleForce\IncreaseResult;
use Symfony\Component\HttpFoundation\Response;

interface ResponseModifyingStrategyInterface extends StrategyInterface
{
    public function modifyResponse(IncreaseResult $increaseResult, Response $response);
}
