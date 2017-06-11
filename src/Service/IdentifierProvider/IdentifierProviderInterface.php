<?php

namespace Maba\Bundle\GentleForceBundle\Service\IdentifierProvider;

use Symfony\Component\HttpFoundation\Request;

interface IdentifierProviderInterface
{
    /**
     * @param Request $request
     *
     * @return string|null
     */
    public function getIdentifier(Request $request);
}
