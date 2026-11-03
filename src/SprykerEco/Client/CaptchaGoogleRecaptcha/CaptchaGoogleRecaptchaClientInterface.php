<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;

interface CaptchaGoogleRecaptchaClientInterface
{
    /**
     * Specification:
     * - Verifies `CaptchaVerificationRequestTransfer.payload` with Google reCAPTCHA siteverify in the current process, with the secret key of the Back Office configuration and `CaptchaVerificationRequestTransfer.ip` as remote IP.
     * - v2 checkbox: returns `isValid=true` for a successful answer without a score.
     * - v3: returns `isValid=true` with `score` for a successful answer whose action equals `CaptchaVerificationRequestTransfer.context` and whose score reaches the configured minimum; otherwise `isValid=false` with the `action_mismatch` or `score_too_low` error code.
     * - Returns `isValid=false` with `isExpired=true` for an expired or reused token, and `isValid=false` with the Google error code for any other rejected token.
     * - Returns `isValid=false` with `isProviderUnavailable=true` and logs an error when the secret key is empty, the version is not supported, Google cannot be reached, rejects the secret key, or sends its answer above the free quota.
     * - The secret key can be read only in an application that registers `ConfigurationApplicationPlugin`.
     * - Never calls Zed.
     *
     * @api
     */
    public function verifyCaptchaTokenLocally(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer;
}
