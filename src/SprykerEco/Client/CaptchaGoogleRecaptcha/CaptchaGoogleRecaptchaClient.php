<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\CaptchaGoogleRecaptcha;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaFactory getFactory()
 */
class CaptchaGoogleRecaptchaClient extends AbstractClient implements CaptchaGoogleRecaptchaClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function verifyCaptchaTokenLocally(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer
    {
        return $this->getFactory()
            ->createLocalRecaptchaTokenVerifier()
            ->verify($captchaVerificationRequestTransfer);
    }
}
