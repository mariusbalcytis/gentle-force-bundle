<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\Service\RequestIdentifierProvider;
use Symfony\Component\HttpFoundation\Request;

class IdentifierHelper
{
    private $identifierProvider;
    private $request;

    private $identifiers = [];

    public function __construct(RequestIdentifierProvider $identifierProvider, Request $request)
    {
        $this->identifierProvider = $identifierProvider;
        $this->request = $request;
    }

    public function getIdentifier(ListenerConfiguration $configuration)
    {
        $identifiers = [];
        foreach ($configuration->getIdentifierTypes() as $identifierType) {
            $identifier = $this->getConcreteIdentifierByType($identifierType);
            if ($identifier === null) {
                return null;
            }

            $identifiers[$identifierType] = $identifier;
        }
        return json_encode($identifiers);
    }

    private function getConcreteIdentifierByType($identifierType)
    {
        if (array_key_exists($identifierType, $this->identifiers)) {
            return $this->identifiers[$identifierType];
        }

        $identifier = $this->identifierProvider->getIdentifier($identifierType, $this->request);
        $this->identifiers[$identifierType] = $identifier;
        return $identifier;
    }
}
