<?php

namespace Maba\Bundle\GentleForceBundle\Controller;

use Maba\Bundle\GentleForceBundle\Listener\ConfigurationManager;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RecaptchaUnlockController
{
    private $recaptcha;
    private $configurationManager;
    private $recaptchaStrategies;

    public function __construct(
        ReCaptcha $recaptcha,
        ConfigurationManager $configurationManager,
        array $recaptchaStrategies
    ) {
        $this->recaptcha = $recaptcha;
        $this->configurationManager = $configurationManager;
        $this->recaptchaStrategies = $recaptchaStrategies;
    }

    public function unlock(Request $request)
    {
        $response = $request->request->get('g-recaptcha-response');
        if ($response === null) {
            return new JsonResponse(
                ['errors' => ['no_recaptcha_response_provided']],
                Response::HTTP_BAD_REQUEST
            );
        }

        $result = $this->recaptcha->verify($response, $request->getClientIp());
        if (!$result->isSuccess()) {
            return new JsonResponse(
                ['errors' => $result->getErrorCodes()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->configurationManager->resetForStrategies($request, $this->recaptchaStrategies);

        return new Response();
    }
}
