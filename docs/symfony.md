# Symfony Integration Guide

This guide demonstrates how to integrate the Cloudflare Turnstile library with Symfony 7.x.

## Installation

First, install the package via Composer:

```bash
composer require esi/cloudflare-turnstile
```

## Configuration

Create a new configuration file `config/packages/turnstile.yaml`:

```yaml
parameters:
    turnstile.site_key: '%env(TURNSTILE_SITE_KEY)%'
    turnstile.secret_key: '%env(TURNSTILE_SECRET_KEY)%'
```

Add your Turnstile credentials to your .env file:

```env
TURNSTILE_SITE_KEY=your_site_key_here
TURNSTILE_SECRET_KEY=your_secret_key_here
```

## Service Configuration

Add to your config/services.yaml:

```yaml
services:
    Esi\CloudflareTurnstile\Turnstile:
        arguments:
            $httpClient: '@http_client'
            $requestFactory: '@Psr\Http\Message\RequestFactoryInterface'
            $streamFactory: '@Psr\Http\Message\StreamFactoryInterface'
            $secretKey: !service
                class: Esi\CloudflareTurnstile\ValueObjects\SecretKey
                arguments: ['%turnstile.secret_key%']
```

## Constraint and Validator

Create a custom constraint:

```php
namespace App\Security\Turnstile;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TurnstileConstraint extends Constraint
{
    public string $message = 'The security challenge was not completed successfully.';
}
```

Create the validator:

```php
namespace App\Security\Turnstile;

use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class TurnstileValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Turnstile $turnstile,
        private readonly RequestStack $requestStack,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TurnstileConstraint) {
            throw new UnexpectedTypeException($constraint, TurnstileConstraint::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        
        try {
            $config = new VerifyConfiguration(
                new Token((string) $value),
                new IpAddress($request?->getClientIp() ?? '127.0.0.1')
            );

            $response = $this->turnstile->verify($config);

            if (!$response->isSuccess()) {
                $this->context->buildViolation($constraint->message)
                    ->setCode('TURNSTILE_INVALID')
                    ->addViolation();
            }
        } catch (\Exception $e) {
            $this->context->buildViolation('An error occurred while validating the security challenge.')
                ->setCode('TURNSTILE_ERROR')
                ->addViolation();
        }
    }
}
```

## Form Type

Create a custom form type for Turnstile:

```php
namespace App\Form;

use App\Security\Turnstile\TurnstileConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TurnstileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('cf-turnstile-response', HiddenType::class, [
            'constraints' => [
                new TurnstileConstraint(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
        ]);
    }
}
```

## Login Form Type

Create a login form that includes the Turnstile:

```php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

final class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('turnstile', TurnstileType::class);
    }
}
```

## Controller

```php
namespace App\Controller;

use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Your login logic here
        }

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
```

## Twig Template

Create templates/security/login.html.twig:

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <div class="login-form">
        {{ form_start(form) }}
            {{ form_row(form.email) }}
            {{ form_row(form.password) }}
            
            {# Turnstile widget #}
            <div class="cf-turnstile" data-sitekey="{{ turnstile_site_key }}"></div>
            {{ form_errors(form.turnstile) }}
            {{ form_widget(form.turnstile) }}
            
            <button type="submit">Login</button>
        {{ form_end(form) }}
    </div>

    {% if error %}
        <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
    {% endif %}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
{% endblock %}
```

## Twig Extension (Optional)

Create a Twig extension to expose the site key:

```php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TurnstileExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $siteKey
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('turnstile_site_key', fn () => $this->siteKey),
        ];
    }
}
```

Register it in `services.yaml`:

```yaml
services:
    App\Twig\TurnstileExtension:
        arguments:
            $siteKey: '%turnstile.site_key%'
        tags: ['twig.extension']
```

## Testing

Create a test for your login form:

```php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginWithTurnstile(): void
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/login');
        
        $form = $crawler->selectButton('Login')->form([
            'login_form[email]' => 'test@example.com',
            'login_form[password]' => 'password123',
            'login_form[turnstile][cf-turnstile-response]' => 'test-token',
        ]);
        
        $client->submit($form);
        
        $this->assertResponseRedirects('/dashboard');
    }
}
```

## Error Handling

The Turnstile integration handles these error cases:

* Invalid/missing token
* Network errors
* Server validation errors
* Client-side widget errors

Errors are handled through Symfony's form validation system and will be displayed in your templates using form_errors().

## Security Considerations

* Always validate responses server-side
* Use HTTPS in production
* Store credentials in environment variables
* Consider implementing rate limiting
* Use Symfony's security component properly

## Performance

For better performance:

* Enable HTTP caching
* Use Symfony's profiler in dev to monitor requests
* Consider implementing caching for successful validations
