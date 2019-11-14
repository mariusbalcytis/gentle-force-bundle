<?php

namespace Maba\Bundle\GentleForceBundle\Service;

class IdentifierBuilder
{
    /**
     * @return string
     *
     * @api
     */
    public function buildIdentifier(array $identifiers)
    {
        return json_encode(array_map(function ($value) {
            return (string)$value;
        }, $identifiers));
    }
}
