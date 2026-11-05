<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Creator;

use Generated\Shared\Transfer\CaptchaChallengeRequestTransfer;
use Generated\Shared\Transfer\CaptchaChallengeResponseTransfer;
use Generated\Shared\Transfer\CaptchaWidgetTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Psr\Log\LoggerInterface;
use Spryker\Shared\Captcha\CaptchaConfig;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;

class RecaptchaWidgetCreator implements RecaptchaWidgetCreatorInterface
{
    protected const string TAG_NAME = 'div';

    protected const string NAME_ATTRIBUTE = 'data-captcha-field';

    protected const string ATTRIBUTE_CLASS = 'class';

    protected const string V2_CHECKBOX_CLASS_NAME = 'g-recaptcha';

    protected const string ATTRIBUTE_V2_CHECKBOX_SITE_KEY = 'data-sitekey';

    protected const string V3_CLASS_NAME = 'js-captcha-google-recaptcha';

    protected const string ATTRIBUTE_V3_SITE_KEY = 'data-site-key';

    protected const string ATTRIBUTE_V3_ACTION = 'data-action';

    protected const string ATTRIBUTE_V3_RESPONSE_FIELD_NAME = 'data-response-field-name';

    protected const string V3_LOADER_TAG_NAME = 'captcha-google-recaptcha';

    protected const string V3_SCRIPT_ASSET_PATH = 'js/spryker-zed-captcha-google-recaptcha.js';

    protected const string V3_SCRIPT_URL_PATTERN = '%s?render=%s';

    /**
     * Google accepts only letters, digits, slashes and underscores in a v3 action name.
     */
    protected const string PATTERN_V3_ACTION = '/^[A-Za-z0-9_\/]{1,100}$/D';

    protected const string LOG_MESSAGE_SITE_KEY_MISSING = 'The Google reCAPTCHA site key is not configured, so no CAPTCHA can be shown.';

    protected const string LOG_MESSAGE_VERSION_NOT_SUPPORTED = 'The Google reCAPTCHA version "%s" is not supported. Use one of: %s.';

    protected const string LOG_MESSAGE_CONTEXT_NOT_ACTION_NAME = 'The CAPTCHA context "%s" is not a valid reCAPTCHA v3 action name.';

    public function __construct(
        protected CaptchaGoogleRecaptchaConfig $captchaGoogleRecaptchaConfig,
        protected LoggerInterface $logger,
    ) {
    }

    public function createChallenge(CaptchaChallengeRequestTransfer $captchaChallengeRequestTransfer): CaptchaChallengeResponseTransfer
    {
        $siteKey = $this->captchaGoogleRecaptchaConfig->getSiteKey();

        if ($siteKey === '') {
            return $this->createUnavailableResponse(static::LOG_MESSAGE_SITE_KEY_MISSING);
        }

        $version = $this->captchaGoogleRecaptchaConfig->getVersion();

        if (!in_array($version, CaptchaGoogleRecaptchaConfig::SUPPORTED_VERSIONS, true)) {
            return $this->createUnavailableResponse(
                sprintf(static::LOG_MESSAGE_VERSION_NOT_SUPPORTED, $version, implode(', ', CaptchaGoogleRecaptchaConfig::SUPPORTED_VERSIONS)),
            );
        }

        return match ($version) {
            CaptchaGoogleRecaptchaConfig::VERSION_V2_CHECKBOX => $this->createV2CheckboxChallenge($siteKey),
            CaptchaGoogleRecaptchaConfig::VERSION_V3 => $this->createV3Challenge($siteKey, (string)$captchaChallengeRequestTransfer->getContext()),
        };
    }

    protected function createV2CheckboxChallenge(string $siteKey): CaptchaChallengeResponseTransfer
    {
        return $this->createSuccessfulResponse(
            (new CaptchaWidgetTransfer())
                ->setTagName(static::TAG_NAME)
                ->setAttributes([
                    static::ATTRIBUTE_CLASS => static::V2_CHECKBOX_CLASS_NAME,
                    static::ATTRIBUTE_V2_CHECKBOX_SITE_KEY => $siteKey,
                ])
                ->setNameAttribute(static::NAME_ATTRIBUTE)
                ->setScriptUrls([$this->captchaGoogleRecaptchaConfig->getScriptUrl()]),
        );
    }

    protected function createV3Challenge(string $siteKey, string $context): CaptchaChallengeResponseTransfer
    {
        if (preg_match(static::PATTERN_V3_ACTION, $context) !== 1) {
            return $this->createUnavailableResponse(sprintf(static::LOG_MESSAGE_CONTEXT_NOT_ACTION_NAME, $context));
        }

        return $this->createSuccessfulResponse(
            (new CaptchaWidgetTransfer())
                ->setTagName(static::TAG_NAME)
                ->setAttributes([
                    static::ATTRIBUTE_CLASS => static::V3_CLASS_NAME,
                    static::ATTRIBUTE_V3_SITE_KEY => $siteKey,
                    static::ATTRIBUTE_V3_ACTION => $context,
                    static::ATTRIBUTE_V3_RESPONSE_FIELD_NAME => $this->captchaGoogleRecaptchaConfig->getResponseFieldName(),
                ])
                ->setNameAttribute(static::NAME_ATTRIBUTE)
                ->setLoaderTagName(static::V3_LOADER_TAG_NAME)
                ->setScriptAssetPaths([static::V3_SCRIPT_ASSET_PATH])
                ->setScriptUrls([sprintf(static::V3_SCRIPT_URL_PATTERN, $this->captchaGoogleRecaptchaConfig->getScriptUrl(), rawurlencode($siteKey))]),
        );
    }

    protected function createSuccessfulResponse(CaptchaWidgetTransfer $captchaWidgetTransfer): CaptchaChallengeResponseTransfer
    {
        return (new CaptchaChallengeResponseTransfer())
            ->setIsSuccessful(true)
            ->setWidget($captchaWidgetTransfer);
    }

    protected function createUnavailableResponse(string $logMessage): CaptchaChallengeResponseTransfer
    {
        $this->logger->error($logMessage);

        return (new CaptchaChallengeResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage((new MessageTransfer())->setValue(CaptchaConfig::GLOSSARY_KEY_CAPTCHA_ERROR_UNAVAILABLE));
    }
}
