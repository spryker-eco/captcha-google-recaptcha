# CaptchaGoogleRecaptcha Module
[![Latest Stable Version](https://poser.pugx.org/spryker-eco/captcha-google-recaptcha/v/stable.svg)](https://packagist.org/packages/spryker-eco/captcha-google-recaptcha)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

CaptchaGoogleRecaptcha provides a [Google reCAPTCHA](https://developers.google.com/recaptcha) CAPTCHA provider for the
Spryker `Captcha` module. It supports reCAPTCHA v2 (the "I'm not a robot" checkbox) and v3 (an invisible score). It
verifies tokens with Google's official PHP library [`google/recaptcha`](https://github.com/google/recaptcha). The keys
are managed in the Back Office. The `Captcha` module renders the widget on the login forms and checks the token
before the login is processed, this module creates the widget and verifies the token.

## Installation

```bash
composer require spryker-eco/captcha-google-recaptcha
```

The module requires `google/recaptcha` `^1.3.1 || ^2.1.0`: the 2.x line needs PHP 8.4, and on PHP 8.3 Composer
resolves 1.3.1. The module uses only the API that both lines share.

## Google setup

1. In the [Google Cloud console](https://console.cloud.google.com/security/recaptcha), create a reCAPTCHA key of type
   **v2 checkbox** or **v3**.
2. List every domain that shows a login: the Storefront, the Back Office and the Merchant Portal hosts. Keep the
   domain verification of the key on.
3. Copy the site key and the key's secret key.
4. For production traffic, link a billing account to the project. Without billing, Google answers every token with
   "success" once the free quota is used up. The free quota is 10,000 verifications per month for the whole
   organization. This module rejects that answer as unavailable (see below), so the logins that need a CAPTCHA stay
   blocked until the quota resets. In the CAPTCHA mode `always` of the `Captcha` module every login submission is one
   verification.

## Configuration

The keys are settings of the Configuration module. They are on **Configuration → Integrations → Google reCAPTCHA** in
the Back Office:

| Setting | Key | Notes |
|---|---|---|
| reCAPTCHA Version | `integrations:google_recaptcha:credentials:version` | `v2_checkbox` (default) or `v3`. The key pair must be of this version. |
| Site Key | `integrations:google_recaptcha:credentials:site_key` | Public. Published to the Storefront. |
| Secret Key | `integrations:google_recaptcha:credentials:secret_key` | Secret: stored encrypted, never published to the key-value store. |
| Minimum Score (v3) | `integrations:google_recaptcha:credentials:score_threshold` | `0.5` by default. A v3 token with a lower score is rejected. |
| Allowed Hostnames | `integrations:google_recaptcha:verification:allowed_hostnames` | Optional, empty by default. Hosts separated by commas, for example the Storefront, Back Office and Merchant Portal hosts. When set, a token solved on any other host is rejected, even if the domain verification of the key is turned off in the Google console. |

`console configuration:sync` registers the settings; the install recipes run it. A deployment can seed the keys from
environment variables instead of the Back Office:

```bash
console configuration:import-environment \
    integrations:google_recaptcha:credentials:site_key=RECAPTCHA_SITE_KEY \
    integrations:google_recaptcha:credentials:secret_key=RECAPTCHA_SECRET_KEY
```

The Storefront reads the site key and the version from the key-value store, so it sees a change after the
`sync.storage.configuration` queue has been processed.

CAPTCHA is off by default. Set `$config[CaptchaConstants::IS_CAPTCHA_ENABLED] = true;` in
`config/Shared/config_default.php`, together with the Redis connection of the `Captcha` module.

## Wiring

```php
// src/Pyz/Client/Captcha/CaptchaDependencyProvider.php
namespace Pyz\Client\Captcha;

use Spryker\Client\Captcha\CaptchaDependencyProvider as SprykerCaptchaDependencyProvider;
use Spryker\Client\CaptchaExtension\Dependency\Plugin\CaptchaProviderPluginInterface;
use SprykerEco\Client\CaptchaGoogleRecaptcha\Plugin\Captcha\GoogleRecaptchaCaptchaProviderPlugin;

class CaptchaDependencyProvider extends SprykerCaptchaDependencyProvider
{
    protected function getCaptchaProviderPlugin(): ?CaptchaProviderPluginInterface
    {
        return new GoogleRecaptchaCaptchaProviderPlugin();
    }
}
```

The Merchant Portal verifies tokens itself and needs the Configuration service, so register
`Spryker\Zed\Configuration\Communication\Plugin\Application\ConfigurationApplicationPlugin` in
`Pyz\Zed\MerchantPortalApplication\MerchantPortalApplicationDependencyProvider::getMerchantPortalApplicationPlugins()`.
The Back Office and the Backend Gateway register it already. Without it, a Zed application verifies tokens through the
Zed gateway and logs a warning for every token.

Then build the frontends:

```bash
console transfer:generate
npm run yves      # Storefront: the lazy `captcha-google-recaptcha` component (v3)
npm run zed       # Back Office: js/spryker-zed-captcha-google-recaptcha.js (v3)
npm run mp:build  # Merchant Portal: Presentation/Components/entry.ts (v3)
```

v2 needs none of these scripts: Google's `api.js` renders the checkbox by itself. For v3, the scripts request a token
when the form is submitted, with the CAPTCHA context as its action. Google advises this because a token expires two
minutes after it is issued.

## Login forms

The `Captcha` module wires the CAPTCHA into the five login forms; this provider needs no login-specific wiring. The
login-form plugins of the `Captcha` module are:

| Application | Plugin |
|---|---|
| Storefront | `Spryker\Yves\Captcha\Plugin\CustomerPage\CustomerLoginFormCaptchaExpanderPlugin`, `Spryker\Yves\Captcha\Plugin\AgentPage\AgentLoginFormCaptchaExpanderPlugin`, `Spryker\Yves\Captcha\Plugin\EventDispatcher\LoginCaptchaEventDispatcherPlugin` |
| Back Office, Merchant Portal | `Spryker\Zed\Captcha\Communication\Plugin\SecurityGui\UserLoginFormCaptchaExpanderPlugin`, `Spryker\Zed\Captcha\Communication\Plugin\SecurityMerchantPortalGui\MerchantPortalLoginFormCaptchaExpanderPlugin`, `Spryker\Zed\Captcha\Communication\Plugin\AgentSecurityMerchantPortalGui\AgentMerchantPortalLoginFormCaptchaExpanderPlugin`, `Spryker\Zed\Captcha\Communication\Plugin\EventDispatcher\LoginCaptchaEventDispatcherPlugin` |

The CAPTCHA mode of the `Captcha` module, the Back Office setting `security:captcha:login:mode`, decides when a login
form shows the widget: `always` (default) on every login, `after_failed_attempts` only while a step-up plugin (for
example the security blocker ones) requires it.

The CAPTCHA context is the identifier of the login form. It is the v3 action name, and a v3 token is accepted only by
the form whose context equals its action:

| Context | Login form |
|---|---|
| `storefront_customer_login` | Storefront customer login |
| `storefront_agent_login` | Storefront agent login |
| `backoffice_user_login` | Back Office login |
| `merchant_portal_user_login` | Merchant Portal login |
| `merchant_portal_agent_login` | Merchant Portal agent login |

Google issues the challenge in the browser, so `GoogleRecaptchaCaptchaProviderPlugin::isChallengePoolSupported()`
returns `false`: the `Captcha` module renders every widget straight from this provider and keeps its challenge pool
empty for it (`captcha:challenge-pool:refill` reports that the provider does not support a challenge pool).

## How a token is verified

The secret key is readable only in an application that registers `ConfigurationApplicationPlugin`:

- In the **Back Office** and the **Merchant Portal**, the provider verifies the token in-process.
- On the **Storefront**, the provider sends the token to Zed (`/captcha-google-recaptcha/gateway/verify-captcha-token`).
  The Backend Gateway then verifies it.

The request to Google's `siteverify` uses a timeout of 2 s to connect and 5 s in total. The result:

A token that is not a base64url string of 20 to 4096 characters is rejected as invalid (`invalid_input_response`)
without a request to Google.

| Answer | Result |
|---|---|
| success for a host that is not in Allowed Hostnames | invalid (`hostname_mismatch`), logged as a warning |
| v2: success without a score | valid |
| v3: success, action equals the context, score ≥ minimum | valid, with `score` |
| v3: other action, or a score below the minimum | invalid (`action_mismatch`, `score_too_low`) |
| `timeout-or-duplicate` | expired (the token was expired or already used) |
| any other Google error code | invalid, with the error code |
| empty or wrong secret key, Google unreachable or answering with an error, unknown version | unavailable, logged as an error |
| success together with error codes, or a v2 success with a score | unavailable, logged as an error: this is Google's answer above the free quota |

Every case except a valid token rejects the login that needs a CAPTCHA, and the account lockout of the
`SecurityBlocker` module applies in all cases. Google accepts a token only once, so the provider sets no replay key.

## Deployment requirements

- **Content-Security-Policy.** A CSP that restricts these directives must allow Google's origins:
  - `script-src`: `https://www.google.com/recaptcha/`, `https://www.gstatic.com/recaptcha/`
  - `frame-src`: `https://www.google.com/recaptcha/`, `https://recaptcha.google.com/recaptcha/`
  - `connect-src`: `https://www.google.com/recaptcha/`
- **Outbound HTTPS.** The Back Office, the Merchant Portal and the Backend Gateway call
  `https://www.google.com/recaptcha/api/siteverify`.
- **Privacy.** reCAPTCHA sends the visitor's IP address and browser signals to Google and sets the `_GRECAPTCHA`
  cookie. The widget loads on every login form in the CAPTCHA mode `always`, and on a login that requires a CAPTCHA
  in the mode `after_failed_attempts`. Cover it in the privacy policy and the cookie notice.
- **v3 badge.** v3 shows Google's reCAPTCHA badge on the pages that load it. Hiding the badge requires the text "This
  site is protected by reCAPTCHA" in the form.

## Documentation

[Spryker Documentation](https://docs.spryker.com)
