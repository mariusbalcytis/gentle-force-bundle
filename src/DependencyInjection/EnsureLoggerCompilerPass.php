<?php

namespace Maba\Bundle\GentleForceBundle\DependencyInjection;

use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EnsureLoggerCompilerPass implements CompilerPassInterface
{
    private $loggerServiceId;

    public function __construct($loggerServiceId)
    {
        $this->loggerServiceId = $loggerServiceId;
    }

    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('logger')) {
            $container->setAlias($this->loggerServiceId, 'logger');
        } else {
            $container->setDefinition($this->loggerServiceId, new Definition(NullLogger::class));
        }
    }
}
