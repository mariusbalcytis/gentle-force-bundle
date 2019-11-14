<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Symfony\Component\HttpFoundation\Request;

class RequestMatcher
{
    public function matches(ListenerConfiguration $configuration, Request $request)
    {
        if (!$this->pathMatches($configuration, $request)) {
            return false;
        }

        if (\count($configuration->getMethods()) > 0 && !$this->methodMatches($configuration, $request)) {
            return false;
        }

        if (\count($configuration->getHosts()) > 0 && !$this->hostMatches($configuration, $request)) {
            return false;
        }

        return true;
    }

    private function pathMatches(ListenerConfiguration $configuration, Request $request)
    {
        return preg_match($configuration->getPathPattern(), $request->getPathInfo()) === 1;
    }

    private function methodMatches(ListenerConfiguration $configuration, Request $request)
    {
        return \in_array($request->getMethod(), $configuration->getMethods(), true);
    }

    private function hostMatches(ListenerConfiguration $configuration, Request $request)
    {
        return \in_array(strtolower($request->getHost()), $configuration->getHosts(), true);
    }
}
