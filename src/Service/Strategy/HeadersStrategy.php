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
    private $content;
    private $contentType;
    private $accessControlAllowOrigin;

    /**
     * @param string|null $waitForHeader
     * @param string|null $requestsAvailableHeader
     * @param string $content
     * @param string $contentType
     */
    public function __construct(
        $waitForHeader = null,
        $requestsAvailableHeader = null,
        $content = '',
        $contentType = 'text/plain',
        $accessControlAllowOrigin = null
    ) {
        $this->waitForHeader = $waitForHeader;
        $this->requestsAvailableHeader = $requestsAvailableHeader;
        $this->content = $content;
        $this->contentType = $contentType;
        $this->accessControlAllowOrigin = $accessControlAllowOrigin;
    }

    public function getRateLimitExceededResponse(CompositeIncreaseResult $result)
    {
        $headers = ['Content-Type' => $this->contentType];

        if ($this->accessControlAllowOrigin) {
            $headers['Access-Control-Allow-Origin'] = $this->accessControlAllowOrigin;
        }

        if ($this->waitForHeader !== null) {
            $headers[$this->waitForHeader] = (string) ceil($result->getWaitForInSeconds());
        }

        return new Response($this->content, Response::HTTP_TOO_MANY_REQUESTS, $headers);
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
                (string) $increaseResult->getUsagesAvailable(),
                true
            );
        }
    }
}
