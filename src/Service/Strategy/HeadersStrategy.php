<?php

namespace Maba\Bundle\GentleForceBundle\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Service\ResponseModifyingStrategyInterface;
use Maba\GentleForce\IncreaseResult;
use Symfony\Component\HttpFoundation\Response;

class HeadersStrategy implements ResponseModifyingStrategyInterface
{
    private $waitForHeader;
    private $requestsAvailableHeader;

    /**
     * @param string|null $waitForHeader
     * @param string|null $requestsAvailableHeader
     */
    public function __construct($waitForHeader = null, $requestsAvailableHeader = null)
    {
        $this->waitForHeader = $waitForHeader;
        $this->requestsAvailableHeader = $requestsAvailableHeader;
    }

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        $headers = [];
        if ($this->waitForHeader !== null) {
            $headers = [
                $this->waitForHeader => $result->getWaitForInSeconds(),
            ];
        }
        return new Response('', Response::HTTP_TOO_MANY_REQUESTS, $headers);
    }

    public function modifyResponse(IncreaseResult $increaseResult, Response $response)
    {
        if ($this->requestsAvailableHeader === null) {
            return;
        }

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
