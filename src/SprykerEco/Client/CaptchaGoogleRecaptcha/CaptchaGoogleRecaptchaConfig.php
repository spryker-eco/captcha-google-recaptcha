<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha;

use Spryker\Client\Kernel\AbstractBundleConfig;

class CaptchaGoogleRecaptchaConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - reCAPTCHA v2 with the "I'm not a robot" checkbox.
     *
     * @api
     */
    public const string VERSION_V2_CHECKBOX = 'v2_checkbox';

    /**
     * Specification:
     * - reCAPTCHA v3: an invisible score, requested when the form is submitted.
     *
     * @api
     */
    public const string VERSION_V3 = 'v3';

    /**
     * Specification:
     * - The reCAPTCHA versions the provider supports.
     *
     * @api
     *
     * @var list<string>
     */
    public const array SUPPORTED_VERSIONS = [
        self::VERSION_V2_CHECKBOX,
        self::VERSION_V3,
    ];

    protected const string CONFIGURATION_KEY_VERSION = 'integrations:google_recaptcha:credentials:version';

    protected const string CONFIGURATION_KEY_SITE_KEY = 'integrations:google_recaptcha:credentials:site_key';

    protected const string CONFIGURATION_KEY_SECRET_KEY = 'integrations:google_recaptcha:credentials:secret_key';

    protected const string CONFIGURATION_KEY_SCORE_THRESHOLD = 'integrations:google_recaptcha:credentials:score_threshold';

    protected const float DEFAULT_SCORE_THRESHOLD = 0.5;

    protected const string SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';

    protected const string SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    protected const string RESPONSE_FIELD_NAME = 'g-recaptcha-response';

    protected const int REQUEST_TIMEOUT_SECONDS = 5;

    protected const int CONNECT_TIMEOUT_SECONDS = 2;

    protected const int MAXIMUM_TOKEN_LENGTH = 10240;

    /**
     * Specification:
     * - Returns the reCAPTCHA version of the Back Office configuration (`integrations:google_recaptcha:credentials:version`), `v2_checkbox` by default.
     * - The site key and the secret key belong to a reCAPTCHA key of this version.
     *
     * @api
     */
    public function getVersion(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_VERSION, static::VERSION_V2_CHECKBOX);
    }

    /**
     * Specification:
     * - Returns the site key of the Back Office configuration (`integrations:google_recaptcha:credentials:site_key`), an empty string when it is not configured.
     *
     * @api
     */
    public function getSiteKey(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_SITE_KEY, '');
    }

    /**
     * Specification:
     * - Returns the secret key of the Back Office configuration (`integrations:google_recaptcha:credentials:secret_key`), an empty string when it is not configured.
     * - The secret key is a secret setting: it is published to no key-value store, so only an application that registers `ConfigurationApplicationPlugin` reads it; elsewhere it reads as an empty string.
     *
     * @api
     */
    public function getSecretKey(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_SECRET_KEY, '');
    }

    /**
     * Specification:
     * - Returns the minimum score a reCAPTCHA v3 token needs, from the Back Office configuration (`integrations:google_recaptcha:credentials:score_threshold`), `0.5` by default.
     * - Returns the default for a value that is not a number, so an emptied setting never lowers the minimum to 0.
     *
     * @api
     */
    public function getScoreThreshold(): float
    {
        $scoreThreshold = $this->getModuleConfig(static::CONFIGURATION_KEY_SCORE_THRESHOLD, static::DEFAULT_SCORE_THRESHOLD);

        return is_numeric($scoreThreshold) ? (float)$scoreThreshold : static::DEFAULT_SCORE_THRESHOLD;
    }

    /**
     * Specification:
     * - Returns the URL of the Google script that renders the v2 checkbox and requests v3 tokens.
     *
     * @api
     */
    public function getScriptUrl(): string
    {
        return static::SCRIPT_URL;
    }

    /**
     * Specification:
     * - Returns the URL of the Google reCAPTCHA siteverify endpoint.
     *
     * @api
     */
    public function getSiteVerifyUrl(): string
    {
        return static::SITE_VERIFY_URL;
    }

    /**
     * Specification:
     * - Returns the name of the request parameter the token is posted in.
     *
     * @api
     */
    public function getResponseFieldName(): string
    {
        return static::RESPONSE_FIELD_NAME;
    }

    /**
     * Specification:
     * - Returns the time in seconds a siteverify request may take in total.
     *
     * @api
     */
    public function getRequestTimeoutSeconds(): int
    {
        return static::REQUEST_TIMEOUT_SECONDS;
    }

    /**
     * Specification:
     * - Returns the time in seconds a siteverify request may take to connect.
     *
     * @api
     */
    public function getConnectTimeoutSeconds(): int
    {
        return static::CONNECT_TIMEOUT_SECONDS;
    }

    /**
     * Specification:
     * - Returns the length in bytes above which a token is rejected without a siteverify request.
     *
     * @api
     */
    public function getMaximumTokenLength(): int
    {
        return static::MAXIMUM_TOKEN_LENGTH;
    }
}
