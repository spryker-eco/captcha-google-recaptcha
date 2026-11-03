<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Resolver;

use SprykerEco\Client\CaptchaGoogleRecaptcha\Verifier\RecaptchaTokenVerifierInterface;

class RecaptchaTokenVerifierResolver implements RecaptchaTokenVerifierResolverInterface
{
    public function __construct(
        protected bool $isConfigurationServiceProvided,
        protected RecaptchaTokenVerifierInterface $localRecaptchaTokenVerifier,
        protected RecaptchaTokenVerifierInterface $zedRecaptchaTokenVerifier,
    ) {
    }

    public function resolveRecaptchaTokenVerifier(): RecaptchaTokenVerifierInterface
    {
        if ($this->isConfigurationServiceProvided) {
            return $this->localRecaptchaTokenVerifier;
        }

        return $this->zedRecaptchaTokenVerifier;
    }
}
