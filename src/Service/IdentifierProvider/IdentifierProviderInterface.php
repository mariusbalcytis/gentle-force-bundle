<?php

namespace Maba\Bundle\GentleForceBundle\Service\IdentifierProvider;

use Symfony\Component\HttpFoundation\Request;

/**
 * @api
 */
interface IdentifierProviderInterface
{
    /**
     * @param Request $request
     *
     * @return string|null
     *
     * @api
     */
    public function getIdentifier(Request $request);
}
