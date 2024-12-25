# Laravel Integration Guide

This guide demonstrates how to integrate the Cloudflare Turnstile library with Laravel 11.x.

> [!IMPORTANT]
> Guide is a work in progress. I have not been able to fully test this integration.

## Installation

```bash
# Install the Turnstile library with Guzzle (recommended for Laravel)
composer require esi/cloudflare-turnstile guzzlehttp/guzzle:^7.0
```

## Configuration

Add your Turnstile credentials to your .env file:
```env
TURNSTILE_SITE_KEY=your_site_key_here
TURNSTILE_SECRET_KEY=your_secret_key_here
```

Create a new configuration file `config/services.php` or add to existing:
```php
return [
    // ... other services
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],
];
```

## Service Provider

Create a new service provider:

```php
namespace App\Providers;

use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

class TurnstileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Turnstile::class, function ($app) {
            $factory = new HttpFactory();
            
            return new Turnstile(
                new Client(),
                $factory,
                $factory,
                new SecretKey(config('services.turnstile.secret_key'))
            );
        });
    }
}
```

Register the service provider in `config/app.php`:
```php
'providers' => [
    // ...
    App\Providers\TurnstileServiceProvider::class,
],
```

## Form Request Validation

Create a form request validator:

```php
namespace App\Http\Requests;

use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TurnstileFormRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'cf-turnstile-response' => ['required', 'string'],
            // ... other validation rules
        ];
    }

    public function validateTurnstile(Turnstile $turnstile): void
    {
        $config = new VerifyConfiguration(
            new Token($this->input('cf-turnstile-response')),
            new IpAddress($this->ip())
        );

        try {
            $response = $turnstile->verify($config);

            if (!$response->isSuccess()) {
                throw ValidationException::withMessages([
                    'turnstile' => ['The security challenge was not completed successfully.']
                ]);
            }
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'turnstile' => ['An error occurred while validating the security challenge.']
            ]);
        }
    }
}
```

## Controller Implementation

```php
namespace App\Http\Controllers;

use App\Http\Requests\TurnstileFormRequest;
use Esi\CloudflareTurnstile\Turnstile;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function login(TurnstileFormRequest $request, Turnstile $turnstile): RedirectResponse
    {
        $request->validateTurnstile($turnstile);

        // Your login logic here
        
        return redirect()->intended('/dashboard');
    }
}
```

Blade Template Integration

```blade
<form method="POST" action="{{ route('login') }}">
    @csrf
    
    <!-- Your form fields -->
    
    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
    
    @error('turnstile')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    
    <button type="submit">Login</button>
</form>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
```

## Testing

For testing, you may want to mock the Turnstile service:

```php
namespace Tests\Feature;

use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\Response;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_with_turnstile(): void
    {
        $this->mock(Turnstile::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->andReturn(new Response([
                    'success' => true,
                    'challenge_ts' => '2024-12-24T00:12:00Z',
                    'hostname' => 'localhost',
                    'error-codes' => [],
                ]));
        });

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'cf-turnstile-response' => 'test-token',
        ]);

        $response->assertRedirect('/dashboard');
    }
}
```

## Error Handling

The Turnstile integration will handle various error cases:

 * Missing token response
 * Invalid token response
 * Network errors
 * Server-side validation errors

These are handled through Laravel's validation system and will be available in your views through the @error directive.

## Security Considerations

* Always validate the Turnstile response server-side
* Use HTTPS in production
* Keep your secret key secure
* Consider rate limiting your endpoints
