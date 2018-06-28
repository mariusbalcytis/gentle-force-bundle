<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures\MockRequestListener;

class FunctionalListenerPrioritiesTest extends FunctionalRequestTestCase
{
    public function testListenerPriorities()
    {
        $container = $this->setUpContainer('listener_priorities');
        /** @var MockRequestListener $listener900 */
        $listener900 = $container->get('listener.priority_900');
        /** @var MockRequestListener $listener800 */
        $listener800 = $container->get('listener.priority_800');
        /** @var MockRequestListener $listener2 */
        $listener2 = $container->get('listener.priority_2');

        $this->assertRequestValid(self::PATH_API1_OK);
        $this->assertSame(1, $listener900->getTriggeredCount());
        $this->assertSame(1, $listener800->getTriggeredCount());
        $this->assertSame(1, $listener2->getTriggeredCount());

        $this->assertRequestInvalid(self::PATH_API1_OK);
        $this->assertSame(2, $listener900->getTriggeredCount());
        $this->assertSame(1, $listener800->getTriggeredCount());
        $this->assertSame(1, $listener2->getTriggeredCount());

        $this->assertRequestValid(self::PATH_API2, self::DEFAULT_IP, self::DEFAULT_USERNAME);
        $this->assertSame(3, $listener900->getTriggeredCount());
        $this->assertSame(2, $listener800->getTriggeredCount());
        $this->assertSame(2, $listener2->getTriggeredCount());

        $this->assertRequestInvalid(self::PATH_API2, self::DEFAULT_IP, self::DEFAULT_USERNAME);
        $this->assertSame(4, $listener900->getTriggeredCount());
        $this->assertSame(3, $listener800->getTriggeredCount());
        $this->assertSame(2, $listener2->getTriggeredCount());
    }
}
