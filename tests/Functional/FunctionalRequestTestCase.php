<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class FunctionalRequestTestCase extends FunctionalTestCase
{
    public const DEFAULT_IP = '10.0.0.1';
    public const ANOTHER_IP = '10.0.0.2';
    public const DEFAULT_USERNAME = 'user1';
    public const ANOTHER_USERNAME = 'user2';
    public const INVALID_USERNAME = 'non_existing_user';

    protected function assertUsagesValid($uri, $countOfUsages)
    {
        for ($i = 0; $i < $countOfUsages; $i++) {
            $this->assertRequestValid($uri);
        }

        $this->assertRequestValid($uri, self::ANOTHER_IP);

        $this->assertRequestInvalid($uri);
    }

    protected function assertRequestValid($uri, $ip = self::DEFAULT_IP, $username = null)
    {
        $response = $this->makeRequest($uri, $ip, $username);
        $this->assertResponseValid($response);
    }

    protected function assertResponseValid(Response $response)
    {
        $this->assertTrue(
            $response->getStatusCode() !== Response::HTTP_TOO_MANY_REQUESTS,
            'Expected valid request'
        );
    }

    protected function assertRequestInvalid($uri, $ip = self::DEFAULT_IP, $username = null)
    {
        $response = $this->makeRequest($uri, $ip, $username);
        $this->assertResponseInvalid($response);
    }

    protected function assertResponseInvalid(Response $response)
    {
        $this->assertSame(
            Response::HTTP_TOO_MANY_REQUESTS,
            $response->getStatusCode(),
            'Expected request to be blocked'
        );
    }

    protected function makeRequest($uri, $ip, $username = null)
    {
        return $this->handleRequest($this->createRequest($uri, $ip, $username));
    }

    protected function createRequest($uri, $ip, $username = null)
    {
        return new Request(
            [], [], [], [], [], [
            'REQUEST_URI' => $uri,
            'REMOTE_ADDR' => $ip,
            'HTTP_PHP_AUTH_USER' => $username,
            'HTTP_PHP_AUTH_PW' => 'pass',
        ]);
    }

    protected function handleRequest(Request $request)
    {
        try {
            return $this->kernel->handle($request);
        } catch (HttpException $exception) {
            return new Response('', $exception->getStatusCode(), $exception->getHeaders());
        }
    }
}
