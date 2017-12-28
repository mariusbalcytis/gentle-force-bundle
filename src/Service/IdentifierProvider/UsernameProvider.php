<?php

namespace Maba\Bundle\GentleForceBundle\Service\IdentifierProvider;

use Maba\Bundle\GentleForceBundle\IdentifierPriority;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UsernameProvider implements IdentifierProviderInterface, PriorityAwareInterface
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getIdentifier(Request $request)
    {
        $token = $this->tokenStorage->getToken();

        return $token !== null && $token->isAuthenticated() && !$token instanceof AnonymousToken
            ? $token->getUsername()
            : null;
    }

    public function getPriority()
    {
        return IdentifierPriority::PRIORITY_AFTER_AUTHORIZATION;
    }
}
