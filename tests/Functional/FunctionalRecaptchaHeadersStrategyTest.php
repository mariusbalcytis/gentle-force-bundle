<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

class FunctionalRecaptchaHeadersStrategyTest extends FunctionalHeadersStrategyTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpContainer('recaptcha_headers_strategy');
    }

    protected function assertResponseBlocked($retryAfter, Response $response)
    {
        $this->assertSame(
            Response::HTTP_TOO_MANY_REQUESTS,
            $response->getStatusCode(),
            'Expected request to be blocked'
        );
        $this->assertSame($retryAfter, $response->headers->get('Retry-After', null));
        $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('Too many requests', $response->getContent());
        $this->assertSame(
            'my_recaptcha_site_key',
            $response->headers->get('My-Recaptcha-Site-Key', null)
        );
    }
}
