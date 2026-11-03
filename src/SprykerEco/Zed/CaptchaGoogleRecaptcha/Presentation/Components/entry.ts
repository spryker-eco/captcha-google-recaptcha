interface GoogleRecaptchaV3 {
    ready(callback: () => void): void;
    execute(siteKey: string, options: { action: string }): Promise<string>;
}

declare const grecaptcha: GoogleRecaptchaV3 | undefined;

const RECAPTCHA_ELEMENT_SELECTOR = '.js-captcha-google-recaptcha';

const releasedForms = new WeakSet<HTMLFormElement>();

const requestToken = (siteKey: string, action: string): Promise<string> =>
    new Promise((resolve, reject) => {
        if (typeof grecaptcha === 'undefined') {
            reject(new Error('The reCAPTCHA script is not loaded.'));

            return;
        }

        grecaptcha.ready(() => grecaptcha.execute(siteKey, { action }).then(resolve, reject));
    });

const findTokenInput = (form: HTMLFormElement, name: string): HTMLInputElement => {
    const tokenInput = form.querySelector<HTMLInputElement>(`input[type="hidden"][name="${name}"]`);

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
    async (event: SubmitEvent) => {
        const form = event.target;
        const recaptchaElement =
            form instanceof HTMLFormElement ? form.querySelector<HTMLElement>(RECAPTCHA_ELEMENT_SELECTOR) : null;

        if (!(form instanceof HTMLFormElement) || !recaptchaElement) {
            return;
        }

        if (releasedForms.delete(form)) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const tokenInput = findTokenInput(form, recaptchaElement.dataset.responseFieldName ?? '');

        try {
            tokenInput.value = await requestToken(
                recaptchaElement.dataset.siteKey ?? '',
                recaptchaElement.dataset.action ?? '',
            );
        } catch (tokenError) {
            tokenInput.value = '';
            console.error('No reCAPTCHA token could be requested.', tokenError);
        }

        releasedForms.add(form);
        form.requestSubmit(event.submitter);
    },
    true,
);
