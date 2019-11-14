<?php

namespace Maba\Bundle\GentleForceBundle\Service\SuccessMatcher;

use Maba\Bundle\GentleForceBundle\Service\SuccessMatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class ResponseCodeSuccessMatcher implements SuccessMatcherInterface
{
    private $statusCodes;
    private $inverse;

    /**
     * @param bool $inverse
     */
    public function __construct(array $statusCodes, $inverse = false)
    {
        $this->statusCodes = $statusCodes;
        $this->inverse = $inverse;
    }

    /**
     * @return bool
     */
    public function isResponseSuccessful(Response $response)
    {
        return $this->inverse xor \in_array($response->getStatusCode(), $this->statusCodes, true);
    }
}
