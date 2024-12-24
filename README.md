# PHP Cloudflare Turnstile Client

[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/badges/build.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/?branch=main)
[![Continuous Integration](https://github.com/ericsizemore/cloudflare-turnstile/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ericsizemore/cloudflare-turnstile/actions/workflows/continuous-integration.yml)
[![Type Coverage](https://shepherd.dev/github/ericsizemore/cloudflare-turnstile/coverage.svg)](https://shepherd.dev/github/ericsizemore/cloudflare-turnstile)
[![Psalm Level](https://shepherd.dev/github/ericsizemore/cloudflare-turnstile/level.svg)](https://shepherd.dev/github/ericsizemore/cloudflare-turnstile)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fericsizemore%2Fcloudflare-turnstile%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/ericsizemore/cloudflare-turnstile/main)
[![Latest Stable Version](https://img.shields.io/packagist/v/esi/cloudflare-turnstile.svg)](https://packagist.org/packages/esi/cloudflare-turnstile)
[![Downloads per Month](https://img.shields.io/packagist/dm/esi/cloudflare-turnstile.svg)](https://packagist.org/packages/esi/cloudflare-turnstile)
[![License](https://img.shields.io/packagist/l/esi/cloudflare-turnstile.svg)](https://packagist.org/packages/esi/cloudflare-turnstile)

`ericsizemore/cloudflare-turnstile` - A PHP library for server-side validation of Cloudflare Turnstile challenges. This library is PSR-18 compatible and framework-agnostic.

> [!IMPORTANT]
> WIP: This library is not yet finished.

>[!NOTE]
> This library requires additional libraries to work successfully. Please see [below](#psr-implementation-libraries).

--- 

### Requirements

* PHP >= 8.2
* Composer
* One of each:
    * PSR-7 HTTP Message implementation
    * PSR-17 HTTP Factory implementation
    * PSR-18 HTTP Client implementation


## Installation

This library is decoupled from any HTTP messaging client by using [PSR-7](https://www.php-fig.org/psr/psr-7/), [PSR-17](https://www.php-fig.org/psr/psr-17/), and [PSR-18](https://www.php-fig.org/psr/psr-18/).

You can install the package via composer:

```bash
# First, install the base package
composer require esi/cloudflare-turnstile

# Then install your preferred PSR implementations. See 'PSR Implementation Libraries' below. For example:

# Option 1: Using Symfony components (recommended for Symfony projects)
composer require symfony/http-client:^7.0 symfony/psr-http-message-bridge:^7.0 nyholm/psr7:^1.0

# Option 2: Using Guzzle (recommended for Laravel projects)
composer require guzzlehttp/guzzle:^7.0 guzzlehttp/psr7:^2.0

# Option 3: Using Laminas
composer require laminas/laminas-diactoros:^3.0 laminas/laminas-http:^2.0
```

#### PSR Implementation Libraries

Below are some recommended libraries that implement the required PSR interfaces. You'll need one implementation of each PSR to use this library.

##### PSR-7: HTTP Message Interface

HTTP message and URI interface implementations:

* [Guzzle PSR-7](https://github.com/guzzle/psr7) - One of the most popular PSR-7 implementations.
* [Laminas Diactoros](https://github.com/laminas/laminas-diactoros) - Former Zend Framework PSR-7 implementation.
* [Nyholm PSR-7](https://github.com/Nyholm/psr7) - Lightweight PSR-7 implementation.
* [Slim PSR-7](https://github.com/slimphp/Slim-Psr7) - Slim Framework's PSR-7 implementation.

##### PSR-17: HTTP Factories

Factory interfaces for PSR-7:

* [Guzzle PSR-7](https://github.com/guzzle/psr7) - Includes PSR-17 factories.
* [Laminas Diactoros](https://github.com/laminas/laminas-diactoros) - Includes PSR-17 factories.
* [Nyholm PSR-7](https://github.com/Nyholm/psr7) - Includes PSR-17 factories.
* [HTTP Factory Utils](https://github.com/php-http/message-factory) - Discovery library for HTTP Factories.

##### PSR-18: HTTP Client

HTTP Client implementations:

* [Symfony HTTP Client](https://github.com/symfony/http-client) - Modern HTTP client with great features.
* [Guzzle](https://github.com/guzzle/guzzle) - Popular HTTP client.
* [PHP HTTP Client](https://github.com/php-http/curl-client) - Curl-based HTTP client.
* [Buzz](https://github.com/kriswallsmith/Buzz) - Lightweight HTTP client.
* [Nyholm PSR-18 Client](https://github.com/Nyholm/psr18-client) - Simple PSR-18 client implementation.

#### Example Installation

Using Symfony components:

```bash
composer require esi/cloudflare-turnstile symfony/http-client:^7.0 symfony/psr-http-message-bridge:^7.0 nyholm/psr7:^1.0
```

Using Guzzle:

```bash
composer require esi/cloudflare-turnstile guzzlehttp/guzzle:^7.0 guzzlehttp/psr7:^2.0
```

Using Laminas:

```bash
composer require esi/cloudflare-turnstile laminas/laminas-diactoros:^3.0 laminas/laminas-http:^2.0
```

## Usage

#### Basic Usage
```php
use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;

// Initialize with your preferred PSR-18 client and PSR-17 factories
$httpClient = new \Your\Preferred\HttpClient();
$requestFactory = new \Your\Preferred\RequestFactory();
$streamFactory = new \Your\Preferred\StreamFactory();

// Create the Turnstile client
$turnstile = new Turnstile(
    $httpClient,
    $requestFactory,
    $streamFactory,
    new SecretKey('your-secret-key')
);

// Create configuration with the response token from the frontend
$config = new VerifyConfiguration(
    new Token('response-token-from-widget')
);

try {
    $response = $turnstile->verify($config);
    
    if ($response->isSuccess()) {
        // Verification successful
        echo "Challenge passed!";
    } else {
        // Verification failed
        echo "Challenge failed: " . implode(', ', $response->getErrorCodes());
    }
} catch (\RuntimeException $e) {
    // Handle JSON decode errors
    echo "Error: " . $e->getMessage();
} catch (\Psr\Http\Client\ClientExceptionInterface $e) {
    // Handle HTTP client errors
    echo "HTTP Error: " . $e->getMessage();
}
```

#### Advanced Usage

##### Using all available options.

```php
use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;

// Initialize with your preferred PSR-18 client and PSR-17 factories
$httpClient = new \Your\Preferred\HttpClient();
$requestFactory = new \Your\Preferred\RequestFactory();
$streamFactory = new \Your\Preferred\StreamFactory();

// Create the Turnstile client
$turnstile = new Turnstile(
    $httpClient,
    $requestFactory,
    $streamFactory,
    new SecretKey('your-secret-key')
);

// Create configuration with all available options
$config = new VerifyConfiguration(
    new Token('response-token-from-widget'),
    new IpAddress('127.0.0.1'),              // Optional: Client IP address
    new IdempotencyKey('unique-request-id'), // Optional: Idempotency key
    [                                        // Optional: Custom data
        'action' => 'login',
        'cdata' => 'custom-verification-data'
    ]
);

$response = $turnstile->verify($config);
```

##### Reading the response.
The response object provides several methods to access verification details:

```php
$response = $turnstile->verify($config);

// Basic verification status
$success = $response->isSuccess();

// Timestamp of the challenge
$timestamp = $response->getChallengeTs();

// Hostname where the challenge was solved
$hostname = $response->getHostname();

// Any error codes returned
$errorCodes = $response->getErrorCodes();

// Optional action name (if set in widget)
$action = $response->getAction();

// Optional custom data (if provided)
$customData = $response->getCdata();

// Enterprise only: metadata
$metadata = $response->getMetadata();

// Access the raw response data
$rawData = $response->getRawData();
```

## Framework Integration Examples

See [docs/laravel.md](docs/laravel.md) and [docs/symfony.md](docs/symfony.md).

## More Information

### FAQ

#### General Questions

**Q: What is Cloudflare Turnstile?**
**A:** Cloudflare Turnstile is a CAPTCHA alternative that provides a user-friendly way to verify that your visitors are human without requiring them to solve puzzles.

**Q: How does it compare to reCAPTCHA?**
**A:** Turnstile is designed to be more privacy-friendly and user-friendly than traditional CAPTCHAs, using multiple non-interactive signals to verify users.

#### Implementation Questions

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

#### Security Questions

**Q: Is it safe to store the secret key in environment variables?**
**A:** Yes, storing sensitive credentials in environment variables is a security best practice. Never commit these values to version control.

**Q: Should I validate responses server-side?**
**A:** Yes, always validate responses server-side. Client-side validation alone is not secure.

### Related Documentation

* [Official Cloudflare Turnstile Documentation](https://developers.cloudflare.com/turnstile/)
* [Get Started Guide for Cloudflare Turnstile](https://developers.cloudflare.com/turnstile/get-started/)
* [Turnstile API Reference](https://developers.cloudflare.com/api/resources/turnstile/subresources/widgets/methods/list/)
* [Widget Configuration Options](https://developers.cloudflare.com/turnstile/concepts/widget/)
* [Client Side Rendering](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/)
* [Error Codes Reference](https://developers.cloudflare.com/turnstile/get-started/server-side-validation/#error-codes)
* [Migrating from ReCAPTCHA or hCAPTCHA](https://developers.cloudflare.com/turnstile/migration/)

### Troubleshooting

**Common Issues**

##### Widget Not Appearing
```html
   <!-- Check if you've included the script correctly -->
   <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
   
   <!-- Verify the div element has the correct class and data attribute -->
   <div class="cf-turnstile" data-sitekey="YOUR_SITE_KEY"></div>
```   

##### Invalid Site Key
```php
// Check your .env file
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA

// Verify configuration loading
echo config('services.turnstile.site_key'); // Laravel
echo $this->getParameter('turnstile.site_key'); // Symfony
```

##### PSR Implementation Conflicts
```bash
# If you encounter version conflicts, try:
composer why-not guzzlehttp/guzzle
composer why-not symfony/http-client

# Or explicitly require compatible versions:
composer require guzzlehttp/guzzle:^7.8
```

##### Network Issues
```php
try {
    $response = $turnstile->verify($config);
} catch (\Psr\Http\Client\NetworkExceptionInterface $e) {
    // Handle network timeouts
    log_error('Turnstile network error: ' . $e->getMessage());
} catch (\Psr\Http\Client\ClientExceptionInterface $e) {
    // Handle other HTTP client errors
    log_error('Turnstile client error: ' . $e->getMessage());
}
```

#### Common Error Responses
```php
// Example error responses and their meanings
[
    'error-codes' => [
        'missing-input-secret' => 'The secret key is missing',
        'invalid-input-secret' => 'The secret key is invalid or malformed',
        'missing-input-response' => 'The response parameter is missing',
        'invalid-input-response' => 'The response parameter is invalid or malformed',
        'bad-request' => 'The request is invalid or malformed',
        'timeout-or-duplicate' => 'The response is no longer valid: either is too old or has been used previously',
    ]
];

// How to handle specific errors
switch ($response->getErrorCodes()[0] ?? '') {
    case 'timeout-or-duplicate':
        // Request new challenge
        break;
    case 'missing-input-secret':
    case 'invalid-input-secret':
        // Configuration error
        log_error('Turnstile configuration error');
        break;
    // etc.
}
```

## About

### Credits

- [Eric Sizemore](https://github.com/ericsizemore)
- [All Contributors](https://github.com/ericsizemore/cloudflare-turnstile/contributors)
- Special thanks to [JetBrains](https://www.jetbrains.com/?from=esi-cloudflare-turnstile) for their Licenses for Open Source Development.

### Contributing

See [CONTRIBUTING](./CONTRIBUTING.md).

Bugs and feature requests are tracked on [GitHub](https://github.com/ericsizemore/cloudflare-turnstile/issues).

### Contributor Covenant Code of Conduct

See [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)

### Backward Compatibility Promise

See [backward-compatibility.md](./backward-compatibility.md) for more information on Backwards Compatibility.

### Changelog

See the [CHANGELOG](./CHANGELOG.md) for more information on what has changed recently.

### License

See the [LICENSE](./LICENSE) for more information on the license that applies to this project.

### Security

See [SECURITY](./SECURITY.md) for more information on the security disclosure process.
