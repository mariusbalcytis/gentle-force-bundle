<?php

namespace Maba\Bundle\GentleForce\Tests\Unit\Service\Strategy;

use Maba\Bundle\GentleForceBundle\Listener\CompositeIncreaseResult;
use Maba\Bundle\GentleForceBundle\Listener\ListenerConfiguration;
use Maba\Bundle\GentleForceBundle\Service\Strategy\HeadersStrategy;
use Maba\GentleForce\Exception\RateLimitReachedException;
use Maba\GentleForce\IncreaseResult;
use Maba\GentleForce\ThrottlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class HeadersStrategyTest extends TestCase
{
    public function testGetRateLimitExceededResponseWithHeader()
    {
        $strategy = new HeadersStrategy('Retry-After');
        $response = $strategy->getRateLimitExceededResponse($this->buildResult());
        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame(15, (int) $response->headers->get('Retry-After'));
    }

    public function testGetRateLimitExceededResponseWithoutHeader()
    {
        $strategy = new HeadersStrategy();
        $response = $strategy->getRateLimitExceededResponse($this->buildResult());
        $this->assertSame(429, $response->getStatusCode());
        $this->assertEmpty(array_diff(
            array_keys($response->headers->all()),
            ['cache-control', 'date', 'content-type']
        ));
    }

    public function testModifyResponse()
    {
        $strategy = new HeadersStrategy(null, 'Requests-Available');
        $response = new Response('my content', 201, ['custom-header' => 'abc']);
        $strategy->modifyResponse($this->buildIncreaseResult(), $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(3, (int) $response->headers->get('Requests-Available'));
    }

    public function testModifyResponseWithoutHeader()
    {
        $strategy = new HeadersStrategy();
        $response = new Response('my content', 201, ['custom-header' => 'abc']);
        $strategy->modifyResponse($this->buildIncreaseResult(), $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEmpty(array_diff(
            array_keys($response->headers->all()),
            ['custom-header', 'cache-control', 'date']
        ));
    }

    private function buildResult()
    {
        $result = new CompositeIncreaseResult();
        $result->addResult(
            $this->buildIncreaseResult(),
            $this->buildConfiguration()
        );
        $result->handleRateLimitReachedException(
            new RateLimitReachedException(15),
            $this->buildConfiguration()
        );
        $result->handleRateLimitReachedException(
            new RateLimitReachedException(10),
            $this->buildConfiguration()
        );

        return $result;
    }

    private function buildIncreaseResult()
    {
        /** @var ThrottlerInterface $throttler */
        $throttler = $this->getMockBuilder(ThrottlerInterface::class)->getMock();

        return new IncreaseResult($throttler, 3, 'limit1', '1');
    }

    private function buildConfiguration()
    {
        return (new ListenerConfiguration())
            ->setLimitsKey('limit1')
            ->setPathPattern('/')
            ->setIdentifierTypes(['ip'])
            ->setStrategyId('headers')
        ;
    }
}
