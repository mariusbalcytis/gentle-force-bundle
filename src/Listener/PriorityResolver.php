<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\IdentifierPriority;
use Maba\Bundle\GentleForceBundle\Service\RequestIdentifierProvider;

class PriorityResolver
{
    private $identifierProvider;

    public function __construct(RequestIdentifierProvider $identifierProvider)
    {
        $this->identifierProvider = $identifierProvider;
    }

    public function resolvePriority(ListenerConfiguration $configuration)
    {
        foreach ($configuration->getIdentifierTypes() as $identifierType) {
            $priority = $this->identifierProvider->getIdentifierPriority($identifierType);
            if ($priority === IdentifierPriority::PRIORITY_AFTER_AUTHORIZATION) {
                return IdentifierPriority::PRIORITY_AFTER_AUTHORIZATION;
            }
        }

        return IdentifierPriority::PRIORITY_NORMAL;
    }
}
