<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Maba\Bundle\GentleForceBundle\Service\SuccessMatcherInterface;

class ListenerConfiguration
{
    /**
     * @var string
     */
    private $pathPattern;

    /**
     * @var array
     */
    private $hosts = [];

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var string
     */
    private $limitsKey;

    /**
     * @var array
     */
    private $identifierTypes;

    /**
     * @var string
     */
    private $strategyId;

    /**
     * @var SuccessMatcherInterface|null
     */
    private $successMatcher;

    /**
     * @var array
     */
    private $roles = [];

    /**
     * @return string
     */
    public function getPathPattern()
    {
        return $this->pathPattern;
    }

    /**
     * @param string $pathPattern
     * @return $this
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;

        return $this;
    }

    /**
     * @return array
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @return $this
     */
    public function setHosts(array $hosts)
    {
        $this->hosts = $hosts;

        return $this;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return $this
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return string
     */
    public function getLimitsKey()
    {
        return $this->limitsKey;
    }

    /**
     * @param string $limitsKey
     * @return $this
     */
    public function setLimitsKey($limitsKey)
    {
        $this->limitsKey = $limitsKey;

        return $this;
    }

    /**
     * @return array
     */
    public function getIdentifierTypes()
    {
        return $this->identifierTypes;
    }

    /**
     * @return $this
     */
    public function setIdentifierTypes(array $identifierTypes)
    {
        $this->identifierTypes = $identifierTypes;

        return $this;
    }

    /**
     * @return string
     */
    public function getStrategyId()
    {
        return $this->strategyId;
    }

    /**
     * @param string $strategyId
     * @return $this
     */
    public function setStrategyId($strategyId)
    {
        $this->strategyId = $strategyId;

        return $this;
    }

    /**
     * @return SuccessMatcherInterface|null
     */
    public function getSuccessMatcher()
    {
        return $this->successMatcher;
    }

    /**
     * @return $this
     */
    public function setSuccessMatcher(SuccessMatcherInterface $successMatcher = null)
    {
        $this->successMatcher = $successMatcher;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }
}
