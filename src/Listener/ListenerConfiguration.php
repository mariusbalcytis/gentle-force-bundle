<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

class ListenerConfiguration
{
    /**
     * @var string
     */
    private $pathPattern;

    /**
     * @var string
     */
    private $limitsKey;

    /**
     * @var array
     */
    private $identifierTypes;

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
     * @param array $identifierTypes
     * @return $this
     */
    public function setIdentifierTypes(array $identifierTypes)
    {
        $this->identifierTypes = $identifierTypes;

        return $this;
    }
}
