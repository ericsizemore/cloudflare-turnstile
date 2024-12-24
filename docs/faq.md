# FAQ

## General Questions

**Q: What is Cloudflare Turnstile?**
**A:** Cloudflare Turnstile is a CAPTCHA alternative that provides a user-friendly way to verify that your visitors are human without requiring them to solve puzzles.

**Q: How does it compare to reCAPTCHA?**
**A:** Turnstile is designed to be more privacy-friendly and user-friendly than traditional CAPTCHAs, using multiple non-interactive signals to verify users.

## Implementation Questions

**Q: Can I use multiple widgets on the same page?**
**A:** Yes, you can have multiple widgets on the same page. Each widget will need its own container:

```html
<div class="cf-turnstile" data-sitekey="KEY1"></div>
<div class="cf-turnstile" data-sitekey="KEY2"></div>
```

**Q: How can I reset a widget?**

**A:** You can reset a widget using the provided JavaScript API:

```javascript
turnstile.reset();
// Or for a specific widget
turnstile.reset('#widget-container');
```

## Security Questions

**Q: Is it safe to store the secret key in environment variables?**
**A:** Yes, storing sensitive credentials in environment variables is a security best practice. Never commit these values to version control.

**Q: Should I validate responses server-side?**
**A:** Yes, always validate responses server-side. Client-side validation alone is not secure.

## Related Documentation

* [Official Cloudflare Turnstile Documentation](https://developers.cloudflare.com/turnstile/)
* [Get Started Guide for Cloudflare Turnstile](https://developers.cloudflare.com/turnstile/get-started/)
* [Turnstile API Reference](https://developers.cloudflare.com/api/resources/turnstile/subresources/widgets/methods/list/)
* [Widget Configuration Options](https://developers.cloudflare.com/turnstile/concepts/widget/)
* [Client Side Rendering](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/)
* [Error Codes Reference](https://developers.cloudflare.com/turnstile/get-started/server-side-validation/#error-codes)
* [Migrating from ReCAPTCHA or hCAPTCHA](https://developers.cloudflare.com/turnstile/migration/)
