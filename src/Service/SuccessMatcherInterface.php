<?php

namespace Maba\Bundle\GentleForceBundle\Service;

use Symfony\Component\HttpFoundation\Response;

interface SuccessMatcherInterface
{
    /**
     * @param Response $response
     * @return bool
     */
    public function isResponseSuccessful(Response $response);
}
