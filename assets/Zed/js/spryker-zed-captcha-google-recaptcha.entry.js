/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

const RECAPTCHA_ELEMENT_SELECTOR = '.js-captcha-google-recaptcha';

const releasedForms = new WeakSet();

const requestToken = (siteKey, action) =>
    new Promise((resolve, reject) => {
        if (typeof window.grecaptcha === 'undefined') {
            reject(new Error('The reCAPTCHA script is not loaded.'));

            return;
        }

        window.grecaptcha.ready(() => window.grecaptcha.execute(siteKey, { action }).then(resolve, reject));
    });

const findTokenInput = (form, name) => {
    const tokenInput = form.querySelector(`input[type="hidden"][name="${name}"]`);

    if (tokenInput) {
        return tokenInput;
    }

    const createdTokenInput = document.createElement('input');
    createdTokenInput.type = 'hidden';
    createdTokenInput.name = name;
    form.append(createdTokenInput);

    return createdTokenInput;
};

document.addEventListener(
    'submit',
    async (event) => {
        const form = event.target;
        const recaptchaElement =
            form instanceof HTMLFormElement ? form.querySelector(RECAPTCHA_ELEMENT_SELECTOR) : null;

        if (!recaptchaElement) {
            return;
        }

        if (releasedForms.delete(form)) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const tokenInput = findTokenInput(form, recaptchaElement.dataset.responseFieldName);

        try {
            tokenInput.value = await requestToken(recaptchaElement.dataset.siteKey, recaptchaElement.dataset.action);
        } catch (tokenError) {
            tokenInput.value = '';
            console.error('No reCAPTCHA token could be requested.', tokenError);
        }

        releasedForms.add(form);
        form.requestSubmit(event.submitter);
    },
    true,
);
