<?php

namespace Maba\Bundle\GentleForceBundle\Service\IdentifierProvider;

use Symfony\Component\HttpFoundation\Request;

class IpProvider implements IdentifierProviderInterface
{
    public function getIdentifier(Request $request)
    {
        return $request->getClientIp();
    }
}
