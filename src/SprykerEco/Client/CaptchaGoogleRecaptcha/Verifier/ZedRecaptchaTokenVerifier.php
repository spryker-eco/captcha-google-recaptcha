<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Verifier;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Zed\CaptchaGoogleRecaptchaStubInterface;

class ZedRecaptchaTokenVerifier implements RecaptchaTokenVerifierInterface
{
    public function __construct(protected CaptchaGoogleRecaptchaStubInterface $captchaGoogleRecaptchaStub)
    {
    }

    public function verify(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer
    {
        return $this->captchaGoogleRecaptchaStub->verifyCaptchaToken($captchaVerificationRequestTransfer);
    }
}
