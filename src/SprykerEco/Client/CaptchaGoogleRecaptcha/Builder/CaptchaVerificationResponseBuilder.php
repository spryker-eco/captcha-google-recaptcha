<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Builder;

use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Spryker\Shared\Captcha\CaptchaConfig;

class CaptchaVerificationResponseBuilder implements CaptchaVerificationResponseBuilderInterface
{
    public function createValidResponse(?float $score): CaptchaVerificationResponseTransfer
    {
        return (new CaptchaVerificationResponseTransfer())
            ->setIsValid(true)
            ->setIsExpired(false)
            ->setIsProviderUnavailable(false)
            ->setScore($score);
    }

    public function createInvalidResponse(string $errorCode, ?float $score = null): CaptchaVerificationResponseTransfer
    {
        return $this->createRejectedResponse($errorCode, CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID)
            ->setScore($score);
    }

    public function createExpiredResponse(string $errorCode): CaptchaVerificationResponseTransfer
    {
        return $this->createRejectedResponse($errorCode, CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_EXPIRED)
            ->setIsExpired(true);
    }

    public function createUnavailableResponse(): CaptchaVerificationResponseTransfer
    {
        return $this->createRejectedResponse(CaptchaConfig::ERROR_CODE_UNAVAILABLE, CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_UNAVAILABLE)
            ->setIsProviderUnavailable(true);
    }

    protected function createRejectedResponse(string $errorCode, string $glossaryKey): CaptchaVerificationResponseTransfer
    {
        return (new CaptchaVerificationResponseTransfer())
            ->setIsValid(false)
            ->setIsExpired(false)
            ->setIsProviderUnavailable(false)
            ->setErrorCode($errorCode)
            ->addMessage((new MessageTransfer())->setValue($glossaryKey));
    }
}
