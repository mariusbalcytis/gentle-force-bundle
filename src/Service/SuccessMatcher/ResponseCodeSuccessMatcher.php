<?php

namespace Maba\Bundle\GentleForceBundle\Service\SuccessMatcher;

use Maba\Bundle\GentleForceBundle\Service\SuccessMatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class ResponseCodeSuccessMatcher implements SuccessMatcherInterface
{
    private $expectedStatusCode;

    /**
     * @param int $expectedStatusCode
     */
    public function __construct($expectedStatusCode)
    {
        $this->expectedStatusCode = $expectedStatusCode;
    }

    /**
     * @param Response $response
     * @return bool
     */
    public function isResponseSuccessful(Response $response)
    {
        return $response->getStatusCode() === $this->expectedStatusCode;
    }
}
