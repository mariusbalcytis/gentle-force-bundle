<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalAdvancedFilteringTest extends FunctionalRequestTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpContainer('advanced_filtering');
    }

    public function testAdvancedFiltering()
    {
        // not in scope:
        $this->assertAdvancedRequestValid('www.example.com', 'GET');

        // limit1 (docs|d.example.com; GET):
        $this->assertAdvancedRequestValid('docs.example.com', 'GET');
        $this->assertAdvancedRequestInvalid('docs.example.com', 'GET');
        $this->assertAdvancedRequestInvalid('d.example.com', 'GET');
        $this->assertAdvancedRequestValid('d.example.com', 'PATCH');

        $this->assertAdvancedRequestValid('www.example.com', 'GET');

        // limit2 (PUT, POST):
        $this->assertAdvancedRequestValid('www.example.com', 'POST');
        $this->assertAdvancedRequestInvalid('www.example.com', 'POST');
        $this->assertAdvancedRequestInvalid('www.example.com', 'PUT');

        $this->assertAdvancedRequestValid('www.example.com', 'GET');

        // limit3 (api.example.com):
        $this->assertAdvancedRequestValid('api.example.com', 'GET');
        $this->assertAdvancedRequestInvalid('api.example.com', 'DELETE');

        $this->assertAdvancedRequestValid('www.example.com', 'GET');

        $this->sleepUpTo(1000);

        $this->assertAdvancedRequestValid('docs.example.com', 'GET');
        $this->assertAdvancedRequestValid('www.example.com', 'POST');
        $this->assertAdvancedRequestValid('api.example.com', 'GET');
    }

    private function createAdvancedRequest($host, $method)
    {
        $request = $this->createRequest(self::PATH_API1, self::DEFAULT_IP);
        $request->server->set('REQUEST_METHOD', $method);
        $request->headers->set('HOST', $host);

        return $request;
    }

    private function assertAdvancedRequestValid($host, $method)
    {
        $this->assertResponseValid($this->handleRequest($this->createAdvancedRequest($host, $method)));
    }

    private function assertAdvancedRequestInvalid($host, $method)
    {
        $this->assertResponseInvalid($this->handleRequest($this->createAdvancedRequest($host, $method)));
    }
}
