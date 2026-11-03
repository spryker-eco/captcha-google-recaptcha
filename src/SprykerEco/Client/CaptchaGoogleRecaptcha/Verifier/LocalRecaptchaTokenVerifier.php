<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Verifier;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod;
use ReCaptcha\Response;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Builder\CaptchaVerificationResponseBuilderInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;
use SprykerEco\Client\CaptchaGoogleRecaptcha\RequestMethod\GuzzleSiteVerifyRequestMethod;

class LocalRecaptchaTokenVerifier implements RecaptchaTokenVerifierInterface
{
    protected const string ERROR_CODE_TIMEOUT_OR_DUPLICATE = 'timeout-or-duplicate';

    protected const string ERROR_CODE_INVALID_INPUT_RESPONSE = 'invalid-input-response';

    protected const string ERROR_CODE_ACTION_MISMATCH = 'action_mismatch';

    protected const string ERROR_CODE_SCORE_TOO_LOW = 'score_too_low';

    protected const string KEY_SCORE = 'score';

    /**
     * The request method logs these itself.
     *
     * @var list<string>
     */
    protected const array TRANSPORT_ERROR_CODES = [
        ReCaptcha::E_CONNECTION_FAILED,
        ReCaptcha::E_BAD_RESPONSE,
        GuzzleSiteVerifyRequestMethod::ERROR_CODE_OVER_FREE_QUOTA,
    ];

    /**
     * @var list<string>
     */
    protected const array UNAVAILABLE_ERROR_CODES = [
        ...self::TRANSPORT_ERROR_CODES,
        'missing-input-secret',
        'invalid-input-secret',
        ReCaptcha::E_INVALID_JSON,
    ];

    protected const string LOG_MESSAGE_SECRET_KEY_MISSING = 'The Google reCAPTCHA secret key is empty in this application, so the token is rejected as unavailable. Configure it in the Back Office; it is readable only where ConfigurationApplicationPlugin is registered.';

    protected const string LOG_MESSAGE_VERSION_NOT_SUPPORTED = 'The Google reCAPTCHA version "%s" is not supported. Use one of: %s.';

    protected const string LOG_MESSAGE_ANSWER_UNAVAILABLE = 'Google reCAPTCHA siteverify could not verify the token: %s.';

    protected const string LOG_MESSAGE_V2_CHECKBOX_ANSWER_WITH_SCORE = 'Google reCAPTCHA answered a v2 checkbox token with a score. Without billing this is the answer above the free quota, so the token is rejected.';

    protected const string LOG_MESSAGE_HOSTNAME_NOT_ALLOWED = 'Google reCAPTCHA verified a token solved on "%s", which is not one of the allowed hostnames, so the token is rejected.';

    public function __construct(
        protected RequestMethod $requestMethod,
        protected CaptchaVerificationResponseBuilderInterface $captchaVerificationResponseBuilder,
        protected CaptchaGoogleRecaptchaConfig $captchaGoogleRecaptchaConfig,
        protected LoggerInterface $logger,
    ) {
    }

    public function verify(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer
    {
        $secretKey = $this->captchaGoogleRecaptchaConfig->getSecretKey();

        if ($secretKey === '') {
            return $this->createLoggedUnavailableResponse(static::LOG_MESSAGE_SECRET_KEY_MISSING);
        }

        $version = $this->captchaGoogleRecaptchaConfig->getVersion();

        if (!in_array($version, CaptchaGoogleRecaptchaConfig::SUPPORTED_VERSIONS, true)) {
            return $this->createLoggedUnavailableResponse(
                sprintf(static::LOG_MESSAGE_VERSION_NOT_SUPPORTED, $version, implode(', ', CaptchaGoogleRecaptchaConfig::SUPPORTED_VERSIONS)),
            );
        }

        $token = (string)$captchaVerificationRequestTransfer->getPayload();

        if ($token !== '' && !$this->isWellFormedToken($token)) {
            return $this->captchaVerificationResponseBuilder->createInvalidResponse($this->normalizeErrorCode(static::ERROR_CODE_INVALID_INPUT_RESPONSE));
        }

        $response = (new ReCaptcha($secretKey, $this->requestMethod))->verify($token, $captchaVerificationRequestTransfer->getIp());

        if (!$response->isSuccess()) {
            return $this->mapErrorCodes($response->getErrorCodes());
        }

        if (!$this->isAllowedHostname($response)) {
            $this->logger->warning(sprintf(static::LOG_MESSAGE_HOSTNAME_NOT_ALLOWED, $response->getHostname()));

            return $this->captchaVerificationResponseBuilder->createInvalidResponse($this->normalizeErrorCode(ReCaptcha::E_HOSTNAME_MISMATCH));
        }

        return match ($version) {
            CaptchaGoogleRecaptchaConfig::VERSION_V2_CHECKBOX => $this->verifyV2CheckboxAnswer($response),
            CaptchaGoogleRecaptchaConfig::VERSION_V3 => $this->verifyV3Answer($response, (string)$captchaVerificationRequestTransfer->getContext()),
        };
    }

    protected function isWellFormedToken(string $token): bool
    {
        $tokenLength = strlen($token);

        return $tokenLength >= $this->captchaGoogleRecaptchaConfig->getMinimumTokenLength()
            && $tokenLength <= $this->captchaGoogleRecaptchaConfig->getMaximumTokenLength()
            && preg_match($this->captchaGoogleRecaptchaConfig->getTokenPattern(), $token) === 1;
    }

    protected function isAllowedHostname(Response $response): bool
    {
        $allowedHostnames = $this->captchaGoogleRecaptchaConfig->getAllowedHostnames();

        return $allowedHostnames === [] || in_array(strtolower((string)$response->getHostname()), $allowedHostnames, true);
    }

    protected function verifyV2CheckboxAnswer(Response $response): CaptchaVerificationResponseTransfer
    {
        if ($this->findScore($response) !== null) {
            return $this->createLoggedUnavailableResponse(static::LOG_MESSAGE_V2_CHECKBOX_ANSWER_WITH_SCORE);
        }

        return $this->captchaVerificationResponseBuilder->createValidResponse(null);
    }

    protected function verifyV3Answer(Response $response, string $context): CaptchaVerificationResponseTransfer
    {
        $score = $this->findScore($response);

        if ($response->getAction() !== $context) {
            return $this->captchaVerificationResponseBuilder->createInvalidResponse(static::ERROR_CODE_ACTION_MISMATCH, $score);
        }

        if ($score === null || $score < $this->captchaGoogleRecaptchaConfig->getScoreThreshold()) {
            return $this->captchaVerificationResponseBuilder->createInvalidResponse(static::ERROR_CODE_SCORE_TOO_LOW, $score);
        }

        return $this->captchaVerificationResponseBuilder->createValidResponse($score);
    }

    protected function findScore(Response $response): ?float
    {
        $score = $response->toArray()[static::KEY_SCORE] ?? null;

        return is_numeric($score) ? (float)$score : null;
    }

    /**
     * @param array<string> $errorCodes
     */
    protected function mapErrorCodes(array $errorCodes): CaptchaVerificationResponseTransfer
    {
        if (array_intersect($errorCodes, static::TRANSPORT_ERROR_CODES) !== []) {
            return $this->captchaVerificationResponseBuilder->createUnavailableResponse();
        }

        if (array_intersect($errorCodes, static::UNAVAILABLE_ERROR_CODES) !== []) {
            return $this->createLoggedUnavailableResponse(sprintf(static::LOG_MESSAGE_ANSWER_UNAVAILABLE, implode(', ', $errorCodes)));
        }

        $errorCode = (string)($errorCodes[0] ?? ReCaptcha::E_UNKNOWN_ERROR);

        if ($errorCode === static::ERROR_CODE_TIMEOUT_OR_DUPLICATE) {
            return $this->captchaVerificationResponseBuilder->createExpiredResponse($this->normalizeErrorCode($errorCode));
        }

        return $this->captchaVerificationResponseBuilder->createInvalidResponse($this->normalizeErrorCode($errorCode));
    }

    protected function normalizeErrorCode(string $errorCode): string
    {
        return str_replace('-', '_', $errorCode);
    }

    protected function createLoggedUnavailableResponse(string $logMessage): CaptchaVerificationResponseTransfer
    {
        $this->logger->error($logMessage);

        return $this->captchaVerificationResponseBuilder->createUnavailableResponse();
    }
}
