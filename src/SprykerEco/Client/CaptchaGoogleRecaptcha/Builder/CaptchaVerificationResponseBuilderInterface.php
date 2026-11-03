<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Builder;

use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;

interface CaptchaVerificationResponseBuilderInterface
{
    public function createValidResponse(?float $score): CaptchaVerificationResponseTransfer;

    public function createInvalidResponse(string $errorCode, ?float $score = null): CaptchaVerificationResponseTransfer;

    public function createExpiredResponse(string $errorCode): CaptchaVerificationResponseTransfer;

    public function createUnavailableResponse(): CaptchaVerificationResponseTransfer;
}
