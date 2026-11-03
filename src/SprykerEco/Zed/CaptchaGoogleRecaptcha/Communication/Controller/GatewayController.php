<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\CaptchaGoogleRecaptcha\Communication\Controller;

use Generated\Shared\Transfer\CaptchaVerificationRequestTransfer;
use Generated\Shared\Transfer\CaptchaVerificationResponseTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \SprykerEco\Zed\CaptchaGoogleRecaptcha\Business\CaptchaGoogleRecaptchaFacadeInterface getFacade()
 */
class GatewayController extends AbstractGatewayController
{
    public function verifyCaptchaTokenAction(CaptchaVerificationRequestTransfer $captchaVerificationRequestTransfer): CaptchaVerificationResponseTransfer
    {
        return $this->getFacade()->verifyCaptchaToken($captchaVerificationRequestTransfer);
    }
}
