import Component from 'ShopUi/models/component';
import { error as logError } from 'ShopUi/app/logger';

interface GoogleRecaptchaV3 {
    ready(callback: () => void): void;
    execute(siteKey: string, options: { action: string }): Promise<string>;
}

declare const grecaptcha: GoogleRecaptchaV3 | undefined;

export default class CaptchaGoogleRecaptcha extends Component {
    protected form: HTMLFormElement | null = null;
    protected tokenInput: HTMLInputElement | null = null;
    protected isSubmitReleased = false;

    protected readyCallback(): void {}

    protected init(): void {
        const recaptchaElement = this.getElementsByClassName(this.jsName)[0] as HTMLElement | undefined;
        this.form = this.closest('form');

        if (!recaptchaElement || !this.form) {
            logError(`${this.name}: the reCAPTCHA element is not inside a form.`);

            return;
        }

        this.tokenInput = this.createTokenInput(recaptchaElement.dataset.responseFieldName ?? '');
        document.addEventListener('submit', (event: SubmitEvent) => this.onSubmit(event, recaptchaElement), true);
    }

    protected onSubmit(event: SubmitEvent, recaptchaElement: HTMLElement): void {
        if (event.target !== this.form) {
            return;
        }

        if (this.isSubmitReleased) {
            this.isSubmitReleased = false;

            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        this.writeToken(recaptchaElement.dataset.siteKey ?? '', recaptchaElement.dataset.action ?? '').finally(() =>
            this.releaseSubmit(event.submitter),
        );
    }

    protected async writeToken(siteKey: string, action: string): Promise<void> {
        try {
            this.tokenInput.value = await this.requestToken(siteKey, action);
        } catch (tokenError) {
            this.tokenInput.value = '';
            logError(`${this.name}: no reCAPTCHA token could be requested.`, tokenError);
        }
    }

    protected requestToken(siteKey: string, action: string): Promise<string> {
        return new Promise((resolve, reject) => {
            if (typeof grecaptcha === 'undefined') {
                reject(new Error('The reCAPTCHA script is not loaded.'));

                return;
            }

            grecaptcha.ready(() => grecaptcha.execute(siteKey, { action }).then(resolve, reject));
        });
    }

    protected releaseSubmit(submitter: HTMLElement | null): void {
        this.isSubmitReleased = true;
        this.form.requestSubmit(submitter);
    }

    protected createTokenInput(name: string): HTMLInputElement {
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = name;
        this.form.append(tokenInput);

        return tokenInput;
    }
}
