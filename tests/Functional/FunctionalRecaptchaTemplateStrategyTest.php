<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

class FunctionalRecaptchaTemplateStrategyTest extends FunctionalRequestTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpContainer('recaptcha_template_strategy');
    }

    public function testRecaptchaTemplateStrategy()
    {
        $this->assertRequestValid(self::PATH_API1, self::DEFAULT_IP);
        $request = $this->createRequest(self::PATH_API1, self::DEFAULT_IP);
        $request->setMethod('POST');
        $response = $this->handleRequest($request);
        $this->assertContains(
            '<html',
            $response->getContent(),
            'Expected response to contain <html> tag'
        );
        $this->assertContains(
            'my_recaptcha_site_key',
            $response->getContent(),
            'Expected response to contain recaptcha site key'
        );
        $this->assertContains(
            '/prefix/gentle-force/recaptcha/unlock',
            $response->getContent(),
            'Expected response to contain unlock URL'
        );
        $this->assertContains(
            'Custom template',
            $response->getContent(),
            'Expected response to be generated from customized template'
        );
    }
}
