<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\CaptchaGoogleRecaptcha;

use Codeception\Actor;
use Codeception\Stub;
use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaClient;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaFactory;
use SprykerEco\Zed\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaDependencyProvider;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 * @method \SprykerEco\Zed\CaptchaGoogleRecaptcha\Business\CaptchaGoogleRecaptchaFacadeInterface getFacade()
 *
 * @SuppressWarnings(PHPMD)
 */
class CaptchaGoogleRecaptchaBusinessTester extends Actor
{
    use _generated\CaptchaGoogleRecaptchaBusinessTesterActions;

    public const string SECRET_KEY = 'test-secret-key-0123456789abcdefghij';

    public const string TOKEN = 'test-recaptcha-token';

    public const string CONTEXT_CUSTOMER = 'customer';

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $siteVerifyHistory = [];

    public function haveCaptchaGoogleRecaptchaClient(string $secretKey, Response ...$siteVerifyAnswers): void
    {
        // Only the Google endpoint and the Back Office configuration are replaced; the facade-to-siteverify path runs for real.
        $this->siteVerifyHistory = [];
        $handlerStack = HandlerStack::create(new MockHandler($siteVerifyAnswers));
        $handlerStack->push(Middleware::history($this->siteVerifyHistory));

        $captchaGoogleRecaptchaConfigStub = Stub::make(CaptchaGoogleRecaptchaConfig::class, [
            'getVersion' => CaptchaGoogleRecaptchaConfig::VERSION_V2_CHECKBOX,
            'getSecretKey' => $secretKey,
        ]);
        $captchaGoogleRecaptchaFactoryStub = Stub::make(CaptchaGoogleRecaptchaFactory::class, [
            'getHttpClient' => new Client(['handler' => $handlerStack]),
            'getConfig' => $captchaGoogleRecaptchaConfigStub,
            'getLogger' => Stub::makeEmpty(LoggerInterface::class),
        ]);

        $captchaGoogleRecaptchaClient = new CaptchaGoogleRecaptchaClient();
        $captchaGoogleRecaptchaClient->setFactory($captchaGoogleRecaptchaFactoryStub);

        $this->setDependency(CaptchaGoogleRecaptchaDependencyProvider::CLIENT_CAPTCHA_GOOGLE_RECAPTCHA, $captchaGoogleRecaptchaClient);
    }

    public function createVerificationRequest(): CaptchaVerificationRequestTransfer
    {
        return (new CaptchaVerificationRequestTransfer())
            ->setPayload(static::TOKEN)
            ->setContext(static::CONTEXT_CUSTOMER);
    }

    public function countSiteVerifyRequests(): int
    {
        return count($this->siteVerifyHistory);
    }
}
