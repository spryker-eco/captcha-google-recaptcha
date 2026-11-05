<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\CaptchaGoogleRecaptcha\Business\Facade;

use Codeception\Test\Unit;
use GuzzleHttp\Psr7\Response;
use Spryker\Shared\Captcha\CaptchaConfig;
use SprykerEcoTest\Zed\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group CaptchaGoogleRecaptcha
 * @group Business
 * @group Facade
 * @group VerifyCaptchaTokenTest
 * Add your own group annotations below this line
 */
class VerifyCaptchaTokenTest extends Unit
{
    protected CaptchaGoogleRecaptchaBusinessTester $tester;

    public function testVerifiesTokenWithGoogleInZed(): void
    {
        // Arrange
        $this->tester->haveCaptchaGoogleRecaptchaClient(
            CaptchaGoogleRecaptchaBusinessTester::SECRET_KEY,
            new Response(200, [], (string)json_encode(['success' => true])),
        );

        // Act
        $captchaVerificationResponseTransfer = $this->tester->getFacade()->verifyCaptchaToken($this->tester->createVerificationRequest());

        // Assert
        $this->assertTrue($captchaVerificationResponseTransfer->getIsValid());
        $this->assertSame(1, $this->tester->countSiteVerifyRequests());
    }

    public function testAnswersUnavailableWithoutRequestWhenSecretKeyIsNotReadable(): void
    {
        // Arrange
        $this->tester->haveCaptchaGoogleRecaptchaClient('');

        // Act
        $captchaVerificationResponseTransfer = $this->tester->getFacade()->verifyCaptchaToken($this->tester->createVerificationRequest());

        // Assert
        $this->assertFalse($captchaVerificationResponseTransfer->getIsValid());
        $this->assertTrue($captchaVerificationResponseTransfer->getIsProviderUnavailable());
        $this->assertSame(CaptchaConfig::ERROR_CODE_UNAVAILABLE, $captchaVerificationResponseTransfer->getErrorCode());
        $this->assertSame(0, $this->tester->countSiteVerifyRequests());
    }
}
