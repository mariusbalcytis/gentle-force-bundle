<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

class ConfigurationRegistry
{
    /**
     * @var array|ListenerConfiguration[]
     */
    private $configurationList = [];

    public function addConfiguration(ListenerConfiguration $configuration)
    {
        $this->configurationList[] = $configuration;
    }

    public function getConfigurationList()
    {
        return $this->configurationList;
    }
}
