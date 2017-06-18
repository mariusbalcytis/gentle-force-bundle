<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class FunctionalRequestTestCase extends FunctionalTestCase
{
    const DEFAULT_IP = '10.0.0.1';
    const ANOTHER_IP = '10.0.0.2';
    const DEFAULT_USERNAME = 'user1';
    const ANOTHER_USERNAME = 'user2';

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
        $this->assertTrue(
            $response->getStatusCode() < 400,
            'Expected valid request'
        );
    }

    protected function assertRequestInvalid($uri, $ip = self::DEFAULT_IP, $username = null)
    {
        $response = $this->makeRequest($uri, $ip, $username);
        $this->assertSame(
            Response::HTTP_TOO_MANY_REQUESTS,
            $response->getStatusCode(),
            'Expected request to be blocked'
        );
    }

    protected function makeRequest($uri, $ip, $username = null)
    {
        $tokenStorage = $this->kernel->getContainer()->get('security.token_storage');
        if ($username !== null) {
            $tokenStorage->setToken(new UsernamePasswordToken(
                $username,
                null,
                'test',
                ['ROLE_USER']
            ));
        } else {
            $tokenStorage->setToken(null);
        }

        $request = new Request([], [], [], [], [], [
            'REQUEST_URI' => $uri,
            'REMOTE_ADDR' => $ip,
        ]);

        try {
            return $this->kernel->handle($request);
        } catch (HttpException $exception) {
            return new Response('', $exception->getStatusCode(), $exception->getHeaders());
        }
    }
}
