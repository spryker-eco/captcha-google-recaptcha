import register from 'ShopUi/app/registry';
export default register(
    'captcha-google-recaptcha',
    () =>
        import(
            /* webpackMode: "lazy" */
            /* webpackChunkName: "captcha-google-recaptcha" */
            './captcha-google-recaptcha'
        ),
);
