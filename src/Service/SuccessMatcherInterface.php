<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use Symfony\Component\HttpFoundation\Response;

/**
 * @api
 */
interface SuccessMatcherInterface
{
    /**
     * @param Response $response
     * @return bool
     *
     * @api
     */
    public function isResponseSuccessful(Response $response);
}
