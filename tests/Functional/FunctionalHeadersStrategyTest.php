<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

class FunctionalHeadersStrategyTest extends FunctionalRequestTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpContainer('headers_strategy');
    }

    public function testHeadersStrategy()
    {
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestsAvailable(1, $response);
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestsAvailable(0, $response);
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertSame(
            Response::HTTP_TOO_MANY_REQUESTS,
            $response->getStatusCode(),
            'Expected request to be blocked'
        );
        $this->assertRetryAfterHeader(0.5, $response);

        $this->sleepUpTo(500);

        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestsAvailable(0, $response);
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertSame(
            Response::HTTP_TOO_MANY_REQUESTS,
            $response->getStatusCode(),
            'Expected request to be blocked'
        );
        $this->assertRetryAfterHeader(0.5, $response);
    }

    protected function assertRequestsAvailable($requestsAvailable, Response $response)
    {
        $this->assertSame(
            [$requestsAvailable],
            $response->headers->get('Request-Limit', [], false)
        );
    }

    protected function assertRetryAfterHeader($retryAfter, Response $response)
    {
        $this->assertSame([$retryAfter], $response->headers->get('Retry-After', [], false));
    }
}
