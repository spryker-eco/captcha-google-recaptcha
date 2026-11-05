<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Verifier;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Psr\Log\LoggerInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Zed\CaptchaGoogleRecaptchaStubInterface;

class ZedRecaptchaTokenVerifier implements RecaptchaTokenVerifierInterface
{
    protected const string LOG_MESSAGE_CONFIGURATION_SERVICE_MISSING = 'This application verifies Google reCAPTCHA tokens through the Zed gateway, because it does not provide the configuration service. Register ConfigurationApplicationPlugin in it to verify the tokens in-process.';

    public function __construct(
        protected CaptchaGoogleRecaptchaStubInterface $captchaGoogleRecaptchaStub,
        protected CaptchaGoogleRecaptchaConfig $captchaGoogleRecaptchaConfig,
        protected LoggerInterface $logger,
    ) {
    }

    public function verify(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer
    {
        if ($this->captchaGoogleRecaptchaConfig->isZedApplication()) {
            $this->logger->warning(static::LOG_MESSAGE_CONFIGURATION_SERVICE_MISSING);
        }

        return $this->captchaGoogleRecaptchaStub->verifyCaptchaToken($captchaVerificationRequestTransfer);
    }
}
