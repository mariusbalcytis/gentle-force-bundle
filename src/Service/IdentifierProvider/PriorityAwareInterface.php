<?php

namespace Maba\Bundle\GentleForceBundle\Service\IdentifierProvider;

/**
 * @api
 */
interface PriorityAwareInterface extends IdentifierProviderInterface
{
    /**
     * Returns one of IdentifierPriority constants.
     *
     * @return string
     */
    public function getPriority();
}
