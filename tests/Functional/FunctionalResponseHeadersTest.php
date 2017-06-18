<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

class FunctionalResponseHeadersTest extends FunctionalRequestTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpContainer('response_headers');
    }

    public function testResponseHeaders()
    {
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestsAvailable(2, $response);
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestsAvailable(1, $response);
        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestsAvailable(0, $response);
        $this->assertRequestInvalid(self::PATH_API1);

        $this->sleepUpTo(400);

        $response = $this->makeRequest(self::PATH_API1, self::DEFAULT_IP);
        $this->assertRequestsAvailable(0, $response);
        $this->assertRequestInvalid(self::PATH_API1);
    }

    private function assertRequestsAvailable($requestsAvailable, Response $response)
    {
        $this->assertSame(
            [$requestsAvailable],
            $response->headers->get('Requests-Available', [], false)
        );
    }
}
