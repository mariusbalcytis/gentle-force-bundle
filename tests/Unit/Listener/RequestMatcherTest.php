<?php

namespace Maba\Bundle\GentleForce\Tests\Unit\Listener;

use Maba\Bundle\GentleForceBundle\Listener\ListenerConfiguration;
use Maba\Bundle\GentleForceBundle\Listener\RequestMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestMatcherTest extends TestCase
{
    /**
     * @var RequestMatcher
     */
    private $matcher;

    /**
     * @var ListenerConfiguration
     */
    private $configuration;

    public function setUp(): void
    {
        $this->matcher = new RequestMatcher();
        $this->configuration = (new ListenerConfiguration())->setPathPattern('#^/[a-z]{2}/api/rest/v1/resource$#');
    }

    /**
     * @param bool $expected
     *
     * @dataProvider dataProviderForPathMatch
     */
    public function testPathMatch($expected, Request $request)
    {
        $result = $this->matcher->matches($this->configuration, $request);
        $this->assertEquals($expected, $result);
    }

    public function dataProviderForPathMatch()
    {
        return [
            'case_empty_request' => [
                true,
                Request::create('/lt/api/rest/v1/resource'),
            ],
            'case_request_has_get_parameters' => [
                true,
                Request::create('/lt/api/rest/v1/resource', 'GET', ['param' => 'value']),
            ],
        ];
    }
}
