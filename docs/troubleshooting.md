# Troubleshooting

## Common Issues

### Widget Not Appearing
```html
   <!-- Check if you've included the script correctly -->
   <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
   
   <!-- Verify the div element has the correct class and data attribute -->
   <div class="cf-turnstile" data-sitekey="YOUR_SITE_KEY"></div>
```   

### Invalid Site Key
```php
// Check your .env file
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA

// Verify configuration loading
echo config('services.turnstile.site_key'); // Laravel
echo $this->getParameter('turnstile.site_key'); // Symfony
```

### PSR Implementation Conflicts
```bash
# If you encounter version conflicts, try:
composer why-not guzzlehttp/guzzle
composer why-not symfony/http-client

# Or explicitly require compatible versions:
composer require guzzlehttp/guzzle:^7.8
```

### Network Issues
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

## Error Responses
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
