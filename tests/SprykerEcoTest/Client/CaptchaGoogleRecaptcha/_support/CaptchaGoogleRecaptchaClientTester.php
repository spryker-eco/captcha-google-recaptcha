<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\CaptchaGoogleRecaptcha;

use Codeception\Actor;
use Codeception\Stub;
use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaDependencyProvider;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Plugin\Captcha\GoogleRecaptchaCaptchaProviderPlugin;
use Throwable;

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
 *
 * @SuppressWarnings(PHPMD)
 */
class CaptchaGoogleRecaptchaClientTester extends Actor
{
    use _generated\CaptchaGoogleRecaptchaClientTesterActions;

    public const string SITE_KEY = 'test-site-key-0123456789abcdefghij';

    public const string SECRET_KEY = 'test-secret-key-0123456789abcdefghij';

    public const string TOKEN = 'test-recaptcha-token';

    public const string IP = '203.0.113.10';

    public const string CONTEXT_CUSTOMER = 'customer';

    public const string CONTEXT_USER = 'user';

    public const float SCORE_THRESHOLD = 0.5;

    public const string CHALLENGE_TIMESTAMP = '2026-11-03T10:00:00Z';

    public const string HOSTNAME = 'yves.de.spryker.local';

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $siteVerifyHistory = [];

    /**
     * @var array<int, array{level: string, message: string, context: array<mixed>}>
     */
    protected array $logRecords = [];

    public function haveRecaptchaConfiguration(
        string $version = CaptchaGoogleRecaptchaConfig::VERSION_V2_CHECKBOX,
        string $siteKey = self::SITE_KEY,
        string $secretKey = self::SECRET_KEY,
        float $scoreThreshold = self::SCORE_THRESHOLD,
    ): void {
        $this->mockConfigMethod('getVersion', $version);
        $this->mockConfigMethod('getSiteKey', $siteKey);
        $this->mockConfigMethod('getSecretKey', $secretKey);
        $this->mockConfigMethod('getScoreThreshold', $scoreThreshold);
    }

    public function haveSiteVerifyAnswers(Response|Throwable ...$answers): void
    {
        $this->siteVerifyHistory = [];
        $handlerStack = HandlerStack::create(new MockHandler($answers));
        $handlerStack->push(Middleware::history($this->siteVerifyHistory));

        $this->setDependency(CaptchaGoogleRecaptchaDependencyProvider::CLIENT_HTTP, new Client(['handler' => $handlerStack]));
        $this->haveRecordingLogger();
    }

    /**
     * @param array<string, mixed> $answer
     */
    public function haveSiteVerifyAnswer(array $answer): void
    {
        $this->haveSiteVerifyAnswers(new Response(200, ['Content-Type' => 'application/json'], (string)json_encode($answer)));
    }

    public function haveV2SuccessAnswer(): void
    {
        $this->haveSiteVerifyAnswer(['success' => true, 'challenge_ts' => static::CHALLENGE_TIMESTAMP, 'hostname' => static::HOSTNAME]);
    }

    public function haveV3SuccessAnswer(float $score, string $action = self::CONTEXT_CUSTOMER): void
    {
        $this->haveSiteVerifyAnswer([
            'success' => true,
            'score' => $score,
            'action' => $action,
            'challenge_ts' => static::CHALLENGE_TIMESTAMP,
            'hostname' => static::HOSTNAME,
        ]);
    }

    public function verifyCaptchaTokenLocally(string $token = self::TOKEN, string $context = self::CONTEXT_CUSTOMER): CaptchaVerificationResponseTransfer
    {
        return $this->getClient()->verifyCaptchaTokenLocally($this->createVerificationRequest($token, $context));
    }

    public function createVerificationRequest(string $token = self::TOKEN, string $context = self::CONTEXT_CUSTOMER): CaptchaVerificationRequestTransfer
    {
        return (new CaptchaVerificationRequestTransfer())
            ->setPayload($token)
            ->setContext($context)
            ->setIp(static::IP);
    }

    public function createPlugin(): GoogleRecaptchaCaptchaProviderPlugin
    {
        $googleRecaptchaCaptchaProviderPlugin = new GoogleRecaptchaCaptchaProviderPlugin();
        $googleRecaptchaCaptchaProviderPlugin->setFactory($this->getFactory());

        return $googleRecaptchaCaptchaProviderPlugin;
    }

    /**
     * @return list<\Psr\Http\Message\RequestInterface>
     */
    public function getSiteVerifyRequests(): array
    {
        return array_values(array_map(fn (array $transaction): RequestInterface => $transaction['request'], $this->siteVerifyHistory));
    }

    /**
     * @return array<string, string>
     */
    public function getSiteVerifyRequestFields(RequestInterface $request): array
    {
        parse_str((string)$request->getBody(), $fields);

        return $fields;
    }

    /**
     * @return array<int, array{level: string, message: string, context: array<mixed>}>
     */
    public function getLogRecords(string $level): array
    {
        return array_values(array_filter($this->logRecords, fn (array $logRecord): bool => $logRecord['level'] === $level));
    }

    public function assertSecretKeyNotLogged(): void
    {
        foreach ($this->logRecords as $logRecord) {
            $this->assertStringNotContainsString(static::SECRET_KEY, $logRecord['message'] . json_encode($logRecord['context']));
        }
    }

    public function haveRecordingLogger(): void
    {
        $this->logRecords = [];
        $loggerMock = $this->createLoggerMock();

        $this->mockFactoryMethod('getLogger', $loggerMock);
    }

    protected function createLoggerMock(): LoggerInterface
    {
        $record = function (string $level): callable {
            return function (string $message, array $context = []) use ($level): void {
                $this->logRecords[] = ['level' => $level, 'message' => $message, 'context' => $context];
            };
        };

        return Stub::makeEmpty(LoggerInterface::class, [
            'error' => $record('error'),
            'warning' => $record('warning'),
        ]);
    }
}
