<?php

namespace Maba\Bundle\GentleForceBundle\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\ResponseModifyingStrategyInterface;
use Maba\GentleForce\IncreaseResult;
use Symfony\Component\HttpFoundation\Response;

class HeadersStrategy implements ResponseModifyingStrategyInterface
{
    private $waitForHeader = 'Wait-For';
    private $requestsAvailableHeader = 'Requests-Available';

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        return new Response('', Response::HTTP_TOO_MANY_REQUESTS, [
            $this->waitForHeader => $result->getWaitForInSeconds(),
        ]);
    }

    public function modifyResponse(IncreaseResult $increaseResult, Response $response)
    {
        $currentValue = $response->headers->get($this->requestsAvailableHeader);
        if (
            $currentValue === null
            || is_numeric($currentValue) && $increaseResult->getUsagesAvailable() < $currentValue
        ) {
            $response->headers->set(
                $this->requestsAvailableHeader,
                $increaseResult->getUsagesAvailable(),
                true
            );
        }
    }
}
