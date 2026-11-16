<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Checker;

use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;

class RecaptchaConfigurationChecker implements RecaptchaConfigurationCheckerInterface
{
    public function __construct(protected CaptchaGoogleRecaptchaConfig $captchaGoogleRecaptchaConfig)
    {
    }

    public function isConfigured(): bool
    {
        return $this->captchaGoogleRecaptchaConfig->getSiteKey() !== ''
            && $this->captchaGoogleRecaptchaConfig->getSecretKey() !== ''
            && in_array($this->captchaGoogleRecaptchaConfig->getVersion(), CaptchaGoogleRecaptchaConfig::SUPPORTED_VERSIONS, true);
    }
}
