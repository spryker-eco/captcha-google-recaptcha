<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\CaptchaGoogleRecaptcha\Business;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerEco\Zed\CaptchaGoogleRecaptcha\Business\CaptchaGoogleRecaptchaBusinessFactory getFactory()
 */
class CaptchaGoogleRecaptchaFacade extends AbstractFacade implements CaptchaGoogleRecaptchaFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function verifyCaptchaToken(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer
    {
        return $this->getFactory()
            ->getCaptchaGoogleRecaptchaClient()
            ->verifyCaptchaTokenLocally($captchaVerificationRequestTransfer);
    }
}
