<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha;

use GuzzleHttp\ClientInterface;
use ReCaptcha\RequestMethod;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Builder\CaptchaVerificationResponseBuilder;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Builder\CaptchaVerificationResponseBuilderInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Creator\RecaptchaWidgetCreator;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Creator\RecaptchaWidgetCreatorInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\RequestMethod\GuzzleSiteVerifyRequestMethod;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Resolver\RecaptchaTokenVerifierResolver;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Resolver\RecaptchaTokenVerifierResolverInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Verifier\LocalRecaptchaTokenVerifier;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Verifier\RecaptchaTokenVerifierInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Verifier\ZedRecaptchaTokenVerifier;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Zed\CaptchaGoogleRecaptchaStub;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Zed\CaptchaGoogleRecaptchaStubInterface;

/**
 * @method \SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig getConfig()
 */
class CaptchaGoogleRecaptchaFactory extends AbstractFactory
{
    use LoggerTrait;

    public function createRecaptchaWidgetCreator(): RecaptchaWidgetCreatorInterface
    {
        return new RecaptchaWidgetCreator($this->getConfig(), $this->getLogger());
    }

    public function createRecaptchaTokenVerifierResolver(): RecaptchaTokenVerifierResolverInterface
    {
        return new RecaptchaTokenVerifierResolver(
            $this->getIsConfigurationServiceProvided(),
            $this->createLocalRecaptchaTokenVerifier(),
            $this->createZedRecaptchaTokenVerifier(),
        );
    }

    public function createLocalRecaptchaTokenVerifier(): RecaptchaTokenVerifierInterface
    {
        return new LocalRecaptchaTokenVerifier(
            $this->createGuzzleSiteVerifyRequestMethod(),
            $this->createCaptchaVerificationResponseBuilder(),
            $this->getConfig(),
            $this->getLogger(),
        );
    }

    public function createZedRecaptchaTokenVerifier(): RecaptchaTokenVerifierInterface
    {
        return new ZedRecaptchaTokenVerifier($this->createZedStub());
    }

    public function createGuzzleSiteVerifyRequestMethod(): RequestMethod
    {
        return new GuzzleSiteVerifyRequestMethod($this->getHttpClient(), $this->getConfig(), $this->getLogger());
    }

    public function createCaptchaVerificationResponseBuilder(): CaptchaVerificationResponseBuilderInterface
    {
        return new CaptchaVerificationResponseBuilder();
    }

    public function createZedStub(): CaptchaGoogleRecaptchaStubInterface
    {
        return new CaptchaGoogleRecaptchaStub($this->getZedRequestClient());
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->getProvidedDependency(CaptchaGoogleRecaptchaDependencyProvider::CLIENT_HTTP);
    }

    public function getZedRequestClient(): ZedRequestClientInterface
    {
        return $this->getProvidedDependency(CaptchaGoogleRecaptchaDependencyProvider::CLIENT_ZED_REQUEST);
    }

    public function getIsConfigurationServiceProvided(): bool
    {
        return $this->getProvidedDependency(CaptchaGoogleRecaptchaDependencyProvider::IS_CONFIGURATION_SERVICE_PROVIDED);
    }
}
