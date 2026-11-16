<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\CaptchaGoogleRecaptcha;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaClientInterface;

class CaptchaGoogleRecaptchaDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string CLIENT_CAPTCHA_GOOGLE_RECAPTCHA = 'CLIENT_CAPTCHA_GOOGLE_RECAPTCHA';

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addCaptchaGoogleRecaptchaClient($container);

        return $container;
    }

    protected function addCaptchaGoogleRecaptchaClient(Container $container): Container
    {
        $container->set(static::CLIENT_CAPTCHA_GOOGLE_RECAPTCHA, function (Container $container): CaptchaGoogleRecaptchaClientInterface {
            return $container->getLocator()->captchaGoogleRecaptcha()->client();
        });

        return $container;
    }
}
