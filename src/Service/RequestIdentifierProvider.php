<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use InvalidArgumentException;
use Maba\Bundle\GentleForceBundle\Service\IdentifierProvider\IdentifierProviderInterface;
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
     * @param Request $request
     * @return null|string
     *
     * @api
     */
    public function getIdentifier($identifierType, Request $request)
    {
        if (!isset($this->identifierProviders[$identifierType])) {
            throw new InvalidArgumentException(sprintf(
                'Identifier provider for "%s" is not available',
                $identifierType
            ));
        }

        return $this->identifierProviders[$identifierType]->getIdentifier($request);
    }
}
