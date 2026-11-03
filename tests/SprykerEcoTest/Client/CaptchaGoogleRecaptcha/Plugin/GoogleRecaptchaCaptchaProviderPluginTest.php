<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\CaptchaGoogleRecaptcha\Plugin;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CaptchaChallengeRequestTransfer;
use Generated\Shared\Transfer\CaptchaChallengeResponseTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Spryker\Client\Captcha\CaptchaClientInterface;
use Spryker\Client\Captcha\CaptchaDependencyProvider;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;
use Spryker\Shared\Captcha\CaptchaConfig;
use Spryker\Shared\Captcha\CaptchaConstants;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaDependencyProvider;
use SprykerEcoTest\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group CaptchaGoogleRecaptcha
 * @group Plugin
 * @group GoogleRecaptchaCaptchaProviderPluginTest
 * Add your own group annotations below this line
 */
class GoogleRecaptchaCaptchaProviderPluginTest extends Unit
{
    protected const string RESPONSE_FIELD_NAME = 'g-recaptcha-response';

    protected const string SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';

    protected const string ZED_VERIFY_CAPTCHA_TOKEN_URL = '/captcha-google-recaptcha/gateway/verify-captcha-token';

    protected CaptchaGoogleRecaptchaClientTester $tester;

    public function testCreatesV2CheckboxWidget(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();

        // Act
        $captchaChallengeResponseTransfer = $this->createChallenge(CaptchaGoogleRecaptchaClientTester::CONTEXT_CUSTOMER);

        // Assert
        $this->assertTrue($captchaChallengeResponseTransfer->getIsSuccessful());
        $captchaWidgetTransfer = $captchaChallengeResponseTransfer->getWidgetOrFail();
        $this->assertSame('div', $captchaWidgetTransfer->getTagName());
        $this->assertSame(['class' => 'g-recaptcha', 'data-sitekey' => CaptchaGoogleRecaptchaClientTester::SITE_KEY], $captchaWidgetTransfer->getAttributes());
        $this->assertSame('data-captcha-field', $captchaWidgetTransfer->getNameAttribute());
        $this->assertSame([static::SCRIPT_URL], $captchaWidgetTransfer->getScriptUrls());
        $this->assertEmpty($captchaWidgetTransfer->getLoaderTagName());
        $this->assertSame([], $captchaWidgetTransfer->getScriptAssetPaths());
    }

    public function testCreatesV3WidgetBoundToContext(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration(CaptchaGoogleRecaptchaConfig::VERSION_V3);

        // Act
        $captchaChallengeResponseTransfer = $this->createChallenge(CaptchaGoogleRecaptchaClientTester::CONTEXT_USER);

        // Assert
        $this->assertTrue($captchaChallengeResponseTransfer->getIsSuccessful());
        $captchaWidgetTransfer = $captchaChallengeResponseTransfer->getWidgetOrFail();
        $this->assertSame('div', $captchaWidgetTransfer->getTagName());
        $this->assertSame([
            'class' => 'js-captcha-google-recaptcha',
            'data-site-key' => CaptchaGoogleRecaptchaClientTester::SITE_KEY,
            'data-action' => CaptchaGoogleRecaptchaClientTester::CONTEXT_USER,
            'data-response-field-name' => static::RESPONSE_FIELD_NAME,
        ], $captchaWidgetTransfer->getAttributes());
        $this->assertSame('data-captcha-field', $captchaWidgetTransfer->getNameAttribute());
        $this->assertSame([sprintf('%s?render=%s', static::SCRIPT_URL, CaptchaGoogleRecaptchaClientTester::SITE_KEY)], $captchaWidgetTransfer->getScriptUrls());
        $this->assertSame('captcha-google-recaptcha', $captchaWidgetTransfer->getLoaderTagName());
        $this->assertSame(['js/spryker-zed-captcha-google-recaptcha.js'], $captchaWidgetTransfer->getScriptAssetPaths());
    }

    /**
     * @dataProvider provideConfigurationsWithoutWidget
     */
    public function testAnswersUnavailableAndLogsErrorWhenNoWidgetCanBeCreated(string $version, string $siteKey, string $context): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration($version, $siteKey);
        $this->tester->haveRecordingLogger();

        // Act
        $captchaChallengeResponseTransfer = $this->createChallenge($context);

        // Assert
        $this->assertFalse($captchaChallengeResponseTransfer->getIsSuccessful());
        $this->assertNull($captchaChallengeResponseTransfer->getWidget());
        $this->assertSame(CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_UNAVAILABLE, $captchaChallengeResponseTransfer->getMessages()->offsetGet(0)->getValue());
        $this->assertCount(1, $this->tester->getLogRecords('error'));
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideConfigurationsWithoutWidget(): array
    {
        return [
            'empty site key' => [CaptchaGoogleRecaptchaConfig::VERSION_V2_CHECKBOX, '', CaptchaGoogleRecaptchaClientTester::CONTEXT_CUSTOMER],
            'unknown version' => ['v1', CaptchaGoogleRecaptchaClientTester::SITE_KEY, CaptchaGoogleRecaptchaClientTester::CONTEXT_CUSTOMER],
            'v3 context that is no action name' => [CaptchaGoogleRecaptchaConfig::VERSION_V3, CaptchaGoogleRecaptchaClientTester::SITE_KEY, 'customer login'],
        ];
    }

    /**
     * @dataProvider provideSupportedVersions
     */
    public function testWidgetOfEverySupportedVersionPassesCaptchaClient(string $version): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration($version);
        $captchaClient = $this->haveCaptchaClientWithGoogleRecaptchaProvider();

        // Act
        $captchaChallengeResponseTransfer = $captchaClient->createChallenge(
            (new CaptchaChallengeRequestTransfer())->setContext(CaptchaGoogleRecaptchaClientTester::CONTEXT_CUSTOMER),
        );

        // Assert
        $this->assertTrue($captchaChallengeResponseTransfer->getIsSuccessful(), sprintf('The widget of version "%s" is rejected.', $version));
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideSupportedVersions(): array
    {
        return array_combine(
            CaptchaGoogleRecaptchaConfig::SUPPORTED_VERSIONS,
            array_map(fn (string $version): array => [$version], CaptchaGoogleRecaptchaConfig::SUPPORTED_VERSIONS),
        );
    }

    public function testPostsTokenInGoogleResponseField(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $captchaClient = $this->haveCaptchaClientWithGoogleRecaptchaProvider();

        // Act
        $responseFieldName = $captchaClient->findResponseFieldName();

        // Assert
        $this->assertSame(static::RESPONSE_FIELD_NAME, $responseFieldName);
    }

    public function testVerifiesInProcessWhereConfigurationServiceIsProvided(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveV2SuccessAnswer();
        $this->tester->setDependency(CaptchaGoogleRecaptchaDependencyProvider::IS_CONFIGURATION_SERVICE_PROVIDED, true);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->createPlugin()->verify($this->tester->createVerificationRequest());

        // Assert
        $this->assertTrue($captchaVerificationResponseTransfer->getIsValid());
        $this->assertCount(1, $this->tester->getSiteVerifyRequests());
    }

    public function testVerifiesThroughZedWhereConfigurationServiceIsMissing(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveSiteVerifyAnswers();
        $captchaVerificationRequestTransfer = $this->tester->createVerificationRequest();
        $zedRequestClientMock = $this->createMock(ZedRequestClientInterface::class);
        $zedRequestClientMock->expects($this->once())
            ->method('call')
            ->with(static::ZED_VERIFY_CAPTCHA_TOKEN_URL, $captchaVerificationRequestTransfer)
            ->willReturn((new CaptchaVerificationResponseTransfer())->setIsValid(true));
        $this->tester->setDependency(CaptchaGoogleRecaptchaDependencyProvider::IS_CONFIGURATION_SERVICE_PROVIDED, false);
        $this->tester->setDependency(CaptchaGoogleRecaptchaDependencyProvider::CLIENT_ZED_REQUEST, $zedRequestClientMock);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->createPlugin()->verify($captchaVerificationRequestTransfer);

        // Assert
        $this->assertTrue($captchaVerificationResponseTransfer->getIsValid());
        $this->assertCount(0, $this->tester->getSiteVerifyRequests());
    }

    protected function createChallenge(string $context): CaptchaChallengeResponseTransfer
    {
        return $this->tester->createPlugin()->createChallenge((new CaptchaChallengeRequestTransfer())->setContext($context));
    }

    protected function haveCaptchaClientWithGoogleRecaptchaProvider(): CaptchaClientInterface
    {
        $this->tester->setConfig(CaptchaConstants::IS_CAPTCHA_ENABLED, true);
        $this->tester->setDependency(CaptchaDependencyProvider::PLUGIN_CAPTCHA_PROVIDER, $this->tester->createPlugin());

        return $this->tester->getLocator()->captcha()->client();
    }
}
