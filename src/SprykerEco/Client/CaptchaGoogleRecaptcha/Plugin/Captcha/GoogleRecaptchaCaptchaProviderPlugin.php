<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Plugin\Captcha;

use Generated\Shared\Transfer\CaptchaChallengeRequestTransfer;
use Generated\Shared\Transfer\CaptchaChallengeResponseTransfer;
use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Spryker\Client\CaptchaExtension\Dependency\Plugin\CaptchaProviderPluginInterface;
use Spryker\Client\Kernel\AbstractPlugin;

/**
 * @method \SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaFactory getFactory()
 */
class GoogleRecaptchaCaptchaProviderPlugin extends AbstractPlugin implements CaptchaProviderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Renders the reCAPTCHA version of the Back Office configuration: the v2 checkbox, or for v3 an element that requests a token bound to the context as its action when the form is submitted.
     * - Returns `isSuccessful=false` with `captcha.error.unavailable` and logs an error when the site key is empty, the version is not supported, or a v3 context is not a valid action name.
     *
     * @api
     */
    public function createChallenge(CaptchaChallengeRequestTransfer $captchaChallengeRequestTransfer): CaptchaChallengeResponseTransfer
    {
        return $this->getFactory()
            ->createRecaptchaWidgetCreator()
            ->createChallenge($captchaChallengeRequestTransfer);
    }

    /**
     * {@inheritDoc}
     * - Verifies the token with Google reCAPTCHA siteverify: in-process where the application provides the `configuration` service, otherwise through Zed, because the secret key is readable only there.
     * - v3: accepts a token only when its action equals the context and its score reaches the configured minimum.
     * - Rejects Google's answer above the free quota and every provider error as unavailable.
     * - Leaves `replayKey` empty: Google accepts a token only once.
     *
     * @api
     */
    public function verify(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer
    {
        return $this->getFactory()
            ->createRecaptchaTokenVerifier()
            ->verify($captchaVerificationRequestTransfer);
    }

    /**
     * {@inheritDoc}
     * - Returns `g-recaptcha-response`, the parameter Google's script and the v3 element post the token in.
     *
     * @api
     */
    public function getResponseFieldName(): ?string
    {
        return $this->getFactory()->getConfig()->getResponseFieldName();
    }

    /**
     * Specification:
     * - Returns `false`: Google issues the challenge in the browser, so there is nothing to create ahead of time.
     *
     * @api
     */
    public function isChallengePoolSupported(): bool
    {
        return false;
    }
}
