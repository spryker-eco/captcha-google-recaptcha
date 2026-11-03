<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha\Resolver;

use SprykerEco\Client\CaptchaGoogleRecaptcha\Verifier\RecaptchaTokenVerifierInterface;

interface RecaptchaTokenVerifierResolverInterface
{
    public function resolveRecaptchaTokenVerifier(): RecaptchaTokenVerifierInterface;
}
