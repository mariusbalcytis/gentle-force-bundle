<?php

namespace Maba\Bundle\GentleForceBundle\Tests\Functional\Fixtures;

use Symfony\Component\HttpFoundation\Response;

class SimpleController
{
    public function returnResponse($statusCode = 200, $content = '')
    {
        return new Response($content, $statusCode);
    }
}
