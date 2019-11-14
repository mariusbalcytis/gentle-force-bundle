<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use InvalidArgumentException;
use Maba\Bundle\GentleForceBundle\IdentifierPriority;
use Maba\Bundle\GentleForceBundle\Service\IdentifierProvider\IdentifierProviderInterface;
use Maba\Bundle\GentleForceBundle\Service\IdentifierProvider\PriorityAwareInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestIdentifierProvider
{
    /**
     * @var array|IdentifierProviderInterface[]
     */
    private $identifierProviders = [];

    public function registerIdentifierProvider(IdentifierProviderInterface $identifierProvider, $identifierType)
    {
        $this->identifierProviders[$identifierType] = $identifierProvider;
    }

    /**
     * @param string $identifierType
     * @return string|null
     *
     * @api
     */
    public function getIdentifier($identifierType, Request $request)
    {
        return $this->getIdentifierProvider($identifierType)->getIdentifier($request);
    }

    public function getIdentifierPriority($identifierType)
    {
        $identifierProvider = $this->getIdentifierProvider($identifierType);

        if (!$identifierProvider instanceof PriorityAwareInterface) {
            return IdentifierPriority::PRIORITY_NORMAL;
        }

        return $identifierProvider->getPriority();
    }

    private function getIdentifierProvider($identifierType)
    {
        if (!isset($this->identifierProviders[$identifierType])) {
            throw new InvalidArgumentException(sprintf('Identifier provider for "%s" is not available', $identifierType));
        }

        return $this->identifierProviders[$identifierType];
    }
}
