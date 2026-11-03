<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\ClientInterface;
use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

/**
 * @method \SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig getConfig()
 */
class CaptchaGoogleRecaptchaDependencyProvider extends AbstractDependencyProvider
{
    public const string CLIENT_HTTP = 'CLIENT_HTTP';

    public const string CLIENT_ZED_REQUEST = 'CLIENT_ZED_REQUEST';

    public const string IS_CONFIGURATION_SERVICE_PROVIDED = 'IS_CONFIGURATION_SERVICE_PROVIDED';

    /**
     * @uses \Spryker\Client\Configuration\ConfigurationDependencyProvider::SERVICE_CONFIGURATION
     */
    protected const string SERVICE_CONFIGURATION = 'configuration';

    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = parent::provideServiceLayerDependencies($container);
        $container = $this->addHttpClient($container);
        $container = $this->addZedRequestClient($container);
        $container = $this->addIsConfigurationServiceProvidedFlag($container);

        return $container;
    }

    protected function addHttpClient(Container $container): Container
    {
        $container->set(static::CLIENT_HTTP, function (): ClientInterface {
            return new GuzzleHttpClient();
        });

        return $container;
    }

    protected function addZedRequestClient(Container $container): Container
    {
        $container->set(static::CLIENT_ZED_REQUEST, function (Container $container): ZedRequestClientInterface {
            return $container->getLocator()->zedRequest()->client();
        });

        return $container;
    }

    protected function addIsConfigurationServiceProvidedFlag(Container $container): Container
    {
        $container->set(static::IS_CONFIGURATION_SERVICE_PROVIDED, function (Container $container): bool {
            return $container->hasApplicationService(static::SERVICE_CONFIGURATION);
        });

        return $container;
    }
}
