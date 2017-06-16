<?php

namespace Maba\Bundle\GentleForceBundle;

use Maba\Bundle\GentleForceBundle\DependencyInjection\StrategyValidationCompilerPass;
use Maba\Component\DependencyInjection\AddTaggedCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MabaGentleForceBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'maba_gentle_force.request_identifier_provider',
            'maba_gentle_force.identifier_provider',
            'registerIdentifierProvider',
            ['identifierType']
        ));
        $container->addCompilerPass(new StrategyValidationCompilerPass());
    }
}
