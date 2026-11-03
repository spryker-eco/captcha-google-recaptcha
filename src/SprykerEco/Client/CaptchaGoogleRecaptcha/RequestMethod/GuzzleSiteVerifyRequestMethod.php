<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\RequestMethod;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod;
use ReCaptcha\RequestParameters;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;

class GuzzleSiteVerifyRequestMethod implements RequestMethod
{
    public const string ERROR_CODE_OVER_FREE_QUOTA = 'over-free-quota';

    protected const string HTTP_METHOD_POST = 'POST';

    protected const string HEADER_CONTENT_TYPE = 'Content-Type';

    protected const string CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    protected const int HTTP_STATUS_OK = 200;

    protected const string KEY_SUCCESS = 'success';

    protected const string KEY_ERROR_CODES = 'error-codes';

    protected const string LOG_MESSAGE_REQUEST_FAILED = 'The Google reCAPTCHA siteverify request failed: %s';

    protected const string LOG_MESSAGE_UNEXPECTED_STATUS = 'Google reCAPTCHA siteverify answered with HTTP status %d.';

    protected const string LOG_MESSAGE_SUCCESS_WITH_ERROR_CODES = 'Google reCAPTCHA siteverify answered "success" together with the errors %s. Without billing this is the answer above the free quota, so the token is rejected.';

    public function __construct(
        protected ClientInterface $httpClient,
        protected CaptchaGoogleRecaptchaConfig $captchaGoogleRecaptchaConfig,
        protected LoggerInterface $logger,
    ) {
    }

    public function submit(RequestParameters $params): string
    {
        try {
            $response = $this->httpClient->request(static::HTTP_METHOD_POST, $this->captchaGoogleRecaptchaConfig->getSiteVerifyUrl(), [
                RequestOptions::BODY => $params->toQueryString(),
                RequestOptions::HEADERS => [static::HEADER_CONTENT_TYPE => static::CONTENT_TYPE_FORM],
                RequestOptions::CONNECT_TIMEOUT => $this->captchaGoogleRecaptchaConfig->getConnectTimeoutSeconds(),
                RequestOptions::TIMEOUT => $this->captchaGoogleRecaptchaConfig->getRequestTimeoutSeconds(),
                RequestOptions::HTTP_ERRORS => false,
            ]);
        } catch (GuzzleException $guzzleException) {
            $this->logger->error(sprintf(static::LOG_MESSAGE_REQUEST_FAILED, $guzzleException->getMessage()));

            return $this->createFailureAnswer(ReCaptcha::E_CONNECTION_FAILED);
        }

        if ($response->getStatusCode() !== static::HTTP_STATUS_OK) {
            $this->logger->error(sprintf(static::LOG_MESSAGE_UNEXPECTED_STATUS, $response->getStatusCode()));

            return $this->createFailureAnswer(ReCaptcha::E_BAD_RESPONSE);
        }

        return $this->rejectSuccessWithErrorCodes((string)$response->getBody());
    }

    protected function rejectSuccessWithErrorCodes(string $answer): string
    {
        $answerData = json_decode($answer, true);

        if (!is_array($answerData) || ($answerData[static::KEY_SUCCESS] ?? null) !== true || empty($answerData[static::KEY_ERROR_CODES])) {
            return $answer;
        }

        $this->logger->error(sprintf(static::LOG_MESSAGE_SUCCESS_WITH_ERROR_CODES, json_encode($answerData[static::KEY_ERROR_CODES])));

        return $this->createFailureAnswer(static::ERROR_CODE_OVER_FREE_QUOTA);
    }

    protected function createFailureAnswer(string $errorCode): string
    {
        return json_encode([static::KEY_SUCCESS => false, static::KEY_ERROR_CODES => [$errorCode]], JSON_THROW_ON_ERROR);
    }
}
