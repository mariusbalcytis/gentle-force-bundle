<?php


namespace Maba\Bundle\GentleForceBundle\Listener;


use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RolesMatcher
{

    public function matches(ListenerConfiguration $configuration, TokenStorageInterface $tokenStorage)
    {
        if (\count($configuration->getRoles()) > 0 && !$this->matchRoles($configuration->getRoles(), $tokenStorage))
            return false;
        return true;
    }

    private function matchRoles(array $expectedRoles, TokenStorageInterface $tokenStorage)
    {
        if ($tokenStorage->getToken() === null)
            return false;
        foreach ($expectedRoles as $expectedRole) {
            if (\in_array($expectedRole, $tokenStorage->getToken()->getRoleNames()))
                return true;
        }
        return false;
    }

}