<?php

declare(strict_types=1);

/**
 * This file is part of Esi\CloudflareTurnstile.
 *
 * (c) Eric Sizemore <admin@secondversion.com>
 *
 * This source file is subject to the MIT license. For the full copyright and
 * license information, please view the LICENSE file that was distributed with
 * this source code.
 */

namespace Esi\CloudflareTurnstile\Tests\EndToEnd;

use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;

/**
 * @internal
 */
#[CoversNothing]
#[Group('end-to-end')]
final class TurnstileEndToEndTest extends TestCase
{
    private const INVALID_SECRET = '1x0000000000000000000000000000000000000000';

    private const INVALID_TOKEN = '0x0000000000000000000000000000000000000000';

    private string $secretKey;

    private Turnstile $turnstile;

    protected function setUp(): void
    {
        // Get secret key from environment variable
        $secretKey = getenv('CLOUDFLARE_TURNSTILE_SECRET_KEY');

        if ($secretKey === '' || $secretKey === false) {
            self::markTestSkipped('CLOUDFLARE_TURNSTILE_SECRET_KEY environment variable not set');
        }

        $this->secretKey = $secretKey;

        $client          = new Psr18Client();
        $this->turnstile = new Turnstile(
            $client,
            $client,
            $client,
            new SecretKey($this->secretKey)
        );
    }

    public function testInvalidTokenVerification(): void
    {
        $config = new VerifyConfiguration(
            new Token(self::INVALID_TOKEN)
        );

        $response = $this->turnstile->verify($config);

        self::assertFalse($response->isSuccess());
        self::assertNotEmpty($response->getErrorCodes());
        self::assertContains('invalid-input-response', $response->getErrorCodes());
    }

    public function testMultipleVerificationAttempts(): void
    {
        $config = new VerifyConfiguration(
            new Token(self::INVALID_TOKEN)
        );

        // Make multiple requests to ensure rate limiting isn't triggered
        for ($i = 0; $i < 3; ++$i) {
            $response = $this->turnstile->verify($config);
            self::assertFalse($response->isSuccess());
            self::assertContains('invalid-input-response', $response->getErrorCodes());

            // Add a small delay between requests
            usleep(100000); // 100ms
        }
    }

    /**
     * @group timeout
     */
    public function testTimeout(): void
    {
        // Create client with a very short timeout
        $client = new Psr18Client(null, null, null);
        $client = $client->withOptions([
            'timeout' => 0.001, // 1ms timeout
        ]);

        $turnstileWithTimeout = new Turnstile(
            $client,
            $client,
            $client,
            new SecretKey($this->secretKey)
        );

        $config = new VerifyConfiguration(
            new Token(self::INVALID_TOKEN)
        );

        $this->expectException(\RuntimeException::class);
        $turnstileWithTimeout->verify($config);
    }

    public function testWithCustomData(): void
    {
        $config = new VerifyConfiguration(
            new Token(self::INVALID_TOKEN),
            null,
            null,
            [
                'action' => 'test',
                'cdata'  => 'custom-data-' . time(),
            ]
        );

        $response = $this->turnstile->verify($config);

        self::assertFalse($response->isSuccess());
        self::assertContains('invalid-input-response', $response->getErrorCodes());
    }

    public function testWithCustomIpAddress(): void
    {
        $config = new VerifyConfiguration(
            new Token(self::INVALID_TOKEN),
            new IpAddress('192.0.2.1') // Using TEST-NET-1 IP address
        );

        $response = $this->turnstile->verify($config);

        self::assertFalse($response->isSuccess());
        // The response should still be invalid-input-response, but we're testing that the request works with IP
        self::assertContains('invalid-input-response', $response->getErrorCodes());
    }

    public function testWithIdempotencyKey(): void
    {
        $idempotencyKey = new IdempotencyKey('test-' . time());

        $config = new VerifyConfiguration(
            new Token(self::INVALID_TOKEN),
            null,
            $idempotencyKey
        );

        // First request
        $response1 = $this->turnstile->verify($config);
        // Second request with same idempotency key
        $response2 = $this->turnstile->verify($config);

        // Both responses should be identical
        self::assertSame($response1->isSuccess(), $response2->isSuccess());
        self::assertSame($response1->getErrorCodes(), $response2->getErrorCodes());
    }

    public function testWithInvalidSecretKey(): void
    {
        $client                  = new Psr18Client();
        $turnstileWithInvalidKey = new Turnstile(
            $client,
            $client,
            $client,
            new SecretKey(self::INVALID_SECRET)
        );

        $config = new VerifyConfiguration(
            new Token(self::INVALID_TOKEN)
        );

        $response = $turnstileWithInvalidKey->verify($config);

        self::assertFalse($response->isSuccess());
        self::assertNotEmpty($response->getErrorCodes());
        self::assertContains('invalid-input-secret', $response->getErrorCodes());
    }
}
