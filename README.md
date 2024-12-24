# PHP Cloudflare Turnstile Client

[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/badges/build.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/ericsizemore/cloudflare-turnstile/?branch=main)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue)](https://php.net)
[![Continuous Integration](https://github.com/ericsizemore/cloudflare-turnstile/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ericsizemore/cloudflare-turnstile/actions/workflows/continuous-integration.yml)
[![Type Coverage](https://shepherd.dev/github/ericsizemore/cloudflare-turnstile/coverage.svg)](https://shepherd.dev/github/ericsizemore/cloudflare-turnstile)
[![Psalm Level](https://shepherd.dev/github/ericsizemore/cloudflare-turnstile/level.svg)](https://shepherd.dev/github/ericsizemore/cloudflare-turnstile)
[![Latest Stable Version](https://img.shields.io/packagist/v/esi/cloudflare-turnstile.svg)](https://packagist.org/packages/esi/cloudflare-turnstile)
[![Downloads per Month](https://img.shields.io/packagist/dm/esi/cloudflare-turnstile.svg)](https://packagist.org/packages/esi/cloudflare-turnstile)
[![License](https://img.shields.io/packagist/l/esi/cloudflare-turnstile.svg)](https://packagist.org/packages/esi/cloudflare-turnstile)
[![Coding Standards](https://img.shields.io/badge/cs-PER--CS%202.0-success)](https://www.php-fig.org/per/coding-style/)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--12-success)](https://www.php-fig.org/psr/psr-12/)

`ericsizemore/cloudflare-turnstile` - A PHP library for server-side validation of Cloudflare Turnstile challenges. This library is PSR-18 compatible and framework-agnostic.

> [!IMPORTANT]
> WIP: This library is not yet finished. Not recommended for production yet.

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
composer require guzzlehttp/guzzle:^7.0

# Option 3: Using Laminas
composer require laminas/laminas-diactoros:^3.0 php-http/curl-client:^2.0

# Option 4: Using PHPHttp
composer require nyholm/psr7:^1.0 php-http/curl-client:^2.0

# Option 5: Using Buzz
composer require kriswallsmith/buzz:^1.3 nyholm/psr7:^1.0

# There are various combinations. Guzzle is all in one, while there are various combinations between Symfony, Laminas, PHPHttp, NyHolm, etc.
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

##### PSR-18: HTTP Client

HTTP Client implementations:

* [Symfony HTTP Client](https://github.com/symfony/http-client) - Modern HTTP client with great features.
* [Guzzle](https://github.com/guzzle/guzzle) - Popular HTTP client.
* [PHP-HTTP Curl Client](https://github.com/php-http/curl-client) - cURL-based PSR-18 client.
* [Buzz](https://github.com/kriswallsmith/Buzz) - Lightweight PSR-18 client.
* [PHP-HTTP Socket Client](https://github.com/php-http/socket-client) - Socket-based PSR-18 client.

##### Notes
- Guzzle provides all required PSR implementations in one package.
- Symfony HTTP Client requires a PSR-7 implementation (like Nyholm) to work as PSR-18.
- PHP-HTTP Curl/Socket Client requires a PSR-7/17 implementation to be installed (like Nyholm or guzzlehttp/psr7).
- Some combinations might require additional bridges or adapters.

#### PSR Implementation Compatibility Matrix

| PSR-18 HTTP Client  | PSR-7/17 Implementation | Additional Requirements |
|---------------------|-------------------------|-------------------------|
| Guzzle              | Built-in                | None                    |
| Symfony HTTP Client | Nyholm PSR-7            | psr-http-message-bridge |
| PHP-HTTP Curl       | Any PSR-7/17            | None                    |
| Buzz                | Any PSR-7/17            | None                    |
| Socket Client       | Any PSR-7/17            | None                    |

#### Example Installation

Using Symfony components:

```bash
composer require esi/cloudflare-turnstile symfony/http-client:^7.0 symfony/psr-http-message-bridge:^7.0 nyholm/psr7:^1.0
```

Using Guzzle:

```bash
composer require esi/cloudflare-turnstile guzzlehttp/guzzle:^7.0
```

Using Laminas:

```bash
composer require esi/cloudflare-turnstile laminas/laminas-diactoros:^3.0 php-http/curl-client:^2.0
```

## Usage

#### Basic Usage
```php
use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;

/**
 * // Using Guzzle
 * use GuzzleHttp\Client;
 * use GuzzleHttp\Psr7\HttpFactory;
 * 
 * $client = new Client();
 * $factory = new HttpFactory();
 * $turnstile = new Turnstile($client, $factory, $factory, new SecretKey('your-secret-key'));
 * 
 * // Using Symfony HTTP Client
 * use Symfony\Component\HttpClient\Psr18Client;
 * use Nyholm\Psr7\Factory\Psr17Factory;
 * 
 * $client = new Psr18Client();
 * $factory = new Psr17Factory();
 * $turnstile = new Turnstile($client, $factory, $factory, new SecretKey('your-secret-key'));
 * 
 * // Using PHP-HTTP Curl Client
 * use Http\Client\Curl\Client;
 * use Nyholm\Psr7\Factory\Psr17Factory;
 * 
 * $factory = new Psr17Factory();
 * $client = new Client();
 * $turnstile = new Turnstile($client, $factory, $factory, new SecretKey('your-secret-key'));
 */
 
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
        echo 'Challenge passed!';
    } else {
        // Verification failed
        echo 'Challenge failed: ' . implode(', ', $response->getErrorCodes());
    }
} catch (\RuntimeException $e) {
    // Handle JSON decode errors
    echo 'Error: ' . $e->getMessage();
} catch (\Psr\Http\Client\ClientExceptionInterface $e) {
    // Handle HTTP client errors
    echo 'HTTP Error: ' . $e->getMessage();
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

### Frequently Asked Questions

See [docs/faq.md](docs/faq.md).

### Performance Considerations

See [docs/performance.md](docs/performance.md).

### Troubleshooting

See [docs/troubleshooting.md](docs/troubleshooting.md).

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

### TODO

See [TODO](./TODO.md) for more information on what is planned for future releases.

### Security

See [SECURITY](./SECURITY.md) for more information on the security disclosure process.
