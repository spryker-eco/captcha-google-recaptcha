<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\CaptchaGoogleRecaptcha\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaClientInterface;
use SprykerEco\Zed\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaDependencyProvider;

class CaptchaGoogleRecaptchaBusinessFactory extends AbstractBusinessFactory
{
    public function getCaptchaGoogleRecaptchaClient(): CaptchaGoogleRecaptchaClientInterface
    {
        return $this->getProvidedDependency(CaptchaGoogleRecaptchaDependencyProvider::CLIENT_CAPTCHA_GOOGLE_RECAPTCHA);
    }
}
