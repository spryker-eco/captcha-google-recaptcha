<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\CaptchaGoogleRecaptcha\Client;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Spryker\Shared\Captcha\CaptchaConfig;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;
use SprykerEcoTest\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group CaptchaGoogleRecaptcha
 * @group Client
 * @group VerifyCaptchaTokenLocallyTest
 * Add your own group annotations below this line
 */
class VerifyCaptchaTokenLocallyTest extends Unit
{
    protected const string SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    protected const int MAXIMUM_TOKEN_LENGTH = 10240;

    protected CaptchaGoogleRecaptchaClientTester $tester;

    public function testAcceptsSuccessfulV2Answer(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveV2SuccessAnswer();

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertTrue($captchaVerificationResponseTransfer->getIsValid());
        $this->assertCount(0, $captchaVerificationResponseTransfer->getMessages());
    }

    public function testPostsSecretKeyTokenAndIpAsFormBodyToSiteVerify(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveV2SuccessAnswer();

        // Act
        $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $siteVerifyRequests = $this->tester->getSiteVerifyRequests();
        $this->assertCount(1, $siteVerifyRequests);
        $this->assertSame('POST', $siteVerifyRequests[0]->getMethod());
        $this->assertSame(static::SITE_VERIFY_URL, (string)$siteVerifyRequests[0]->getUri());
        $this->assertSame([
            'secret' => CaptchaGoogleRecaptchaClientTester::SECRET_KEY,
            'response' => CaptchaGoogleRecaptchaClientTester::TOKEN,
            'remoteip' => CaptchaGoogleRecaptchaClientTester::IP,
        ], array_intersect_key($this->tester->getSiteVerifyRequestFields($siteVerifyRequests[0]), ['secret' => 1, 'response' => 1, 'remoteip' => 1]));
    }

    public function testAnswersUnavailableForSuccessfulV2AnswerWithScore(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveSiteVerifyAnswer(['success' => true, 'score' => 0.9]);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertUnavailable($captchaVerificationResponseTransfer);
        $this->assertCount(1, $this->tester->getLogRecords('error'));
    }

    public function testAnswersUnavailableForSuccessfulAnswerWithErrorCodes(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration(CaptchaGoogleRecaptchaConfig::VERSION_V3);
        $this->tester->haveSiteVerifyAnswer([
            'success' => true,
            'score' => 0.9,
            'action' => CaptchaGoogleRecaptchaClientTester::CONTEXT_CUSTOMER,
            'error-codes' => ['Over free quota.'],
        ]);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertUnavailable($captchaVerificationResponseTransfer);
        $this->assertCount(1, $this->tester->getLogRecords('error'));
    }

    public function testAcceptsV3AnswerWithMatchingActionAndScoreAtThreshold(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration(CaptchaGoogleRecaptchaConfig::VERSION_V3);
        $this->tester->haveV3SuccessAnswer(CaptchaGoogleRecaptchaClientTester::SCORE_THRESHOLD);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertTrue($captchaVerificationResponseTransfer->getIsValid());
        $this->assertSame(CaptchaGoogleRecaptchaClientTester::SCORE_THRESHOLD, $captchaVerificationResponseTransfer->getScore());
    }

    public function testRejectsV3AnswerForAnotherContext(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration(CaptchaGoogleRecaptchaConfig::VERSION_V3);
        $this->tester->haveV3SuccessAnswer(0.9, CaptchaGoogleRecaptchaClientTester::CONTEXT_USER);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertRejected($captchaVerificationResponseTransfer, 'action_mismatch', CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID);
    }

    public function testRejectsV3AnswerBelowScoreThreshold(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration(CaptchaGoogleRecaptchaConfig::VERSION_V3);
        $this->tester->haveV3SuccessAnswer(0.3);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertRejected($captchaVerificationResponseTransfer, 'score_too_low', CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID);
        $this->assertSame(0.3, $captchaVerificationResponseTransfer->getScore());
    }

    public function testRejectsV3AnswerWithoutScore(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration(CaptchaGoogleRecaptchaConfig::VERSION_V3);
        $this->tester->haveSiteVerifyAnswer(['success' => true, 'action' => CaptchaGoogleRecaptchaClientTester::CONTEXT_CUSTOMER]);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertRejected($captchaVerificationResponseTransfer, 'score_too_low', CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID);
    }

    /**
     * @dataProvider provideRejectingErrorCodes
     */
    public function testRejectsAnswerWithErrorCode(string $googleErrorCode, bool $isExpired, string $expectedErrorCode, string $expectedGlossaryKey): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveSiteVerifyAnswer(['success' => false, 'error-codes' => [$googleErrorCode]]);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertRejected($captchaVerificationResponseTransfer, $expectedErrorCode, $expectedGlossaryKey);
        $this->assertSame($isExpired, $captchaVerificationResponseTransfer->getIsExpired());
    }

    /**
     * @return array<string, array<string|bool>>
     */
    public function provideRejectingErrorCodes(): array
    {
        return [
            'expired or reused token' => ['timeout-or-duplicate', true, 'timeout_or_duplicate', CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_EXPIRED],
            'invalid token' => ['invalid-input-response', false, 'invalid_input_response', CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID],
            'missing token' => ['missing-input-response', false, 'missing_input_response', CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID],
            'bad request' => ['bad-request', false, 'bad_request', CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID],
            'error code Google adds later' => ['some-new-code', false, 'some_new_code', CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID],
        ];
    }

    /**
     * @dataProvider provideSecretKeyErrorCodes
     */
    public function testAnswersUnavailableAndLogsErrorWhenGoogleRejectsSecretKey(string $googleErrorCode): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveSiteVerifyAnswer(['success' => false, 'error-codes' => [$googleErrorCode]]);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertUnavailable($captchaVerificationResponseTransfer);
        $this->assertCount(1, $this->tester->getLogRecords('error'));
        $this->tester->assertSecretKeyNotLogged();
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideSecretKeyErrorCodes(): array
    {
        return [
            'invalid secret key' => ['invalid-input-secret'],
            'missing secret key' => ['missing-input-secret'],
        ];
    }

    public function testAnswersUnavailableWhenSiteVerifyAnswersWithServerError(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveSiteVerifyAnswers(new Response(500, [], 'Internal Server Error'));

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertUnavailable($captchaVerificationResponseTransfer);
        $this->assertCount(1, $this->tester->getLogRecords('error'));
    }

    public function testAnswersUnavailableWithoutLoggingSecretKeyWhenSiteVerifyCannotBeReached(): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveSiteVerifyAnswers(new ConnectException('Connection timed out', new Request('POST', static::SITE_VERIFY_URL)));

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertUnavailable($captchaVerificationResponseTransfer);
        $this->assertNotEmpty($this->tester->getLogRecords('error'));
        $this->tester->assertSecretKeyNotLogged();
    }

    /**
     * @dataProvider provideIncompleteConfigurations
     */
    public function testAnswersUnavailableWithoutRequestWhenConfigurationIsIncomplete(string $version, string $secretKey): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration($version, CaptchaGoogleRecaptchaClientTester::SITE_KEY, $secretKey);
        $this->tester->haveSiteVerifyAnswers();

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertUnavailable($captchaVerificationResponseTransfer);
        $this->assertCount(0, $this->tester->getSiteVerifyRequests());
        $this->assertCount(1, $this->tester->getLogRecords('error'));
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideIncompleteConfigurations(): array
    {
        return [
            'empty secret key' => [CaptchaGoogleRecaptchaConfig::VERSION_V2_CHECKBOX, ''],
            'unknown version' => ['v1', CaptchaGoogleRecaptchaClientTester::SECRET_KEY],
        ];
    }

    /**
     * @dataProvider provideTokensRejectedWithoutRequest
     */
    public function testRejectsTokenWithoutRequest(string $token, string $expectedErrorCode): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration();
        $this->tester->haveSiteVerifyAnswers();

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally($token);

        // Assert
        $this->assertRejected($captchaVerificationResponseTransfer, $expectedErrorCode, CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_INVALID);
        $this->assertCount(0, $this->tester->getSiteVerifyRequests());
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideTokensRejectedWithoutRequest(): array
    {
        return [
            'empty token' => ['', 'missing_input_response'],
            'token longer than any reCAPTCHA token' => [str_repeat('a', static::MAXIMUM_TOKEN_LENGTH + 1), 'invalid_input_response'],
        ];
    }

    /**
     * @dataProvider provideSupportedVersions
     */
    public function testVerifiesEverySupportedVersion(string $version): void
    {
        // Arrange
        $this->tester->haveRecaptchaConfiguration($version);
        $this->tester->haveSiteVerifyAnswer([
            'success' => true,
            'score' => $version === CaptchaGoogleRecaptchaConfig::VERSION_V3 ? 0.9 : null,
            'action' => CaptchaGoogleRecaptchaClientTester::CONTEXT_CUSTOMER,
        ]);

        // Act
        $captchaVerificationResponseTransfer = $this->tester->verifyCaptchaTokenLocally();

        // Assert
        $this->assertTrue($captchaVerificationResponseTransfer->getIsValid(), sprintf('Version "%s" is not verified.', $version));
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

    protected function assertUnavailable(CaptchaVerificationResponseTransfer $captchaVerificationResponseTransfer): void
    {
        $this->assertFalse($captchaVerificationResponseTransfer->getIsValid());
        $this->assertTrue($captchaVerificationResponseTransfer->getIsProviderUnavailable());
        $this->assertSame(CaptchaConfig::ERROR_CODE_UNAVAILABLE, $captchaVerificationResponseTransfer->getErrorCode());
        $this->assertSame(CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_UNAVAILABLE, $captchaVerificationResponseTransfer->getMessages()->offsetGet(0)->getValue());
    }

    protected function assertRejected(
        CaptchaVerificationResponseTransfer $captchaVerificationResponseTransfer,
        string $expectedErrorCode,
        string $expectedGlossaryKey
    ): void {
        $this->assertFalse($captchaVerificationResponseTransfer->getIsValid());
        $this->assertNotTrue($captchaVerificationResponseTransfer->getIsProviderUnavailable());
        $this->assertSame($expectedErrorCode, $captchaVerificationResponseTransfer->getErrorCode());
        $this->assertSame($expectedGlossaryKey, $captchaVerificationResponseTransfer->getMessages()->offsetGet(0)->getValue());
    }
}
