<?php

namespace Maba\Bundle\GentleForceBundle\Listener;

use Symfony\Component\HttpFoundation\Request;

class RequestMatcher
{
    public function matches(ListenerConfiguration $configuration, Request $request)
    {
        return preg_match($configuration->getPathPattern(), $request->getRequestUri()) === 1;
    }
}
