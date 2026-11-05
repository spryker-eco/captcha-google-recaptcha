<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\CaptchaGoogleRecaptcha\Business;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;

interface CaptchaGoogleRecaptchaFacadeInterface
{
    /**
     * Specification:
     * - Verifies `CaptchaVerificationRequestTransfer.payload` with Google reCAPTCHA siteverify in Zed, see `CaptchaGoogleRecaptchaClientInterface::verifyCaptchaTokenLocally()`.
     * - Serves the Storefront logins through the gateway: Yves cannot read the secret key, a secret setting of the Back Office configuration.
     * - Never calls Zed through a gateway itself.
     *
     * @api
     */
    public function verifyCaptchaToken(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer;
}
