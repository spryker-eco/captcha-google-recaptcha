<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Zed;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

class CaptchaGoogleRecaptchaStub implements CaptchaGoogleRecaptchaStubInterface
{
    /**
     * @uses \SprykerEco\Zed\CaptchaGoogleRecaptcha\Communication\Controller\GatewayController::verifyCaptchaTokenAction()
     */
    protected const string ZED_VERIFY_CAPTCHA_TOKEN = '/captcha-google-recaptcha/gateway/verify-captcha-token';

    public function __construct(protected ZedRequestClientInterface $zedRequestClient)
    {
    }

    public function verifyCaptchaToken(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\CaptchaVerificationResponseTransfer $captchaVerificationResponseTransfer */
        $captchaVerificationResponseTransfer = $this->zedRequestClient->call(static::ZED_VERIFY_CAPTCHA_TOKEN, $captchaVerificationRequestTransfer);

        return $captchaVerificationResponseTransfer;
    }
}
