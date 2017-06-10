<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class RequestIdentifierProvider
{
    public function getIdentifier(Request $request)
    {
        return $request->getClientIp();
    }
}
