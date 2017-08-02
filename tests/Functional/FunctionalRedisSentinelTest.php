<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional;

use Predis\Configuration\OptionsInterface;

class FunctionalRedisSentinelTest extends FunctionalThrottlerTestCase
{
    public function testConnection()
    {
        $container = $this->setUpContainer('redis_sentinel');
        /** @var OptionsInterface $options */
        $options = $container->get('maba_gentle_force.redis_client')->getOptions();

        $this->assertTrue($options->defined('replication'));
        $this->assertTrue($options->defined('service'));
        $this->assertTrue($options->defined('parameters'));
    }
}
