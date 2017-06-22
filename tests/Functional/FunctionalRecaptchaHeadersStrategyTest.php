<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

class FunctionalRecaptchaHeadersStrategyTest extends FunctionalHeadersStrategyTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpContainer('recaptcha_headers_strategy');
    }

    protected function assertRetryAfterHeader($retryAfter, Response $response)
    {
        parent::assertRetryAfterHeader($retryAfter, $response);
        $this->assertSame(
            ['my_recaptcha_site_key'],
            $response->headers->get('My-Recaptcha-Site-Key', [], false)
        );
    }
}
