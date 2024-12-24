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

namespace Esi\CloudflareTurnstile\Tests\Integration\HttpClient;

use Esi\CloudflareTurnstile\Response;
use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(Turnstile::class)]
#[CoversClass(Response::class)]
#[CoversClass(IdempotencyKey::class)]
#[CoversClass(IpAddress::class)]
#[CoversClass(SecretKey::class)]
#[CoversClass(Token::class)]
#[CoversClass(VerifyConfiguration::class)]
final class SymfonyClientIntegrationTest extends TestCase
{
    private MockHttpClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private Turnstile $turnstile;

    protected function setUp(): void
    {
        $this->mockHttpClient = new MockHttpClient();
        $this->psr17Factory   = new Psr17Factory();

        $client = new Psr18Client($this->mockHttpClient);

        $this->turnstile = new Turnstile(
            $client,
            $this->psr17Factory,
            $this->psr17Factory,
            new SecretKey('test-secret-key')
        );
    }

    public function testFailedVerification(): void
    {
        $this->mockHttpClient->setResponseFactory(
            new MockResponse(
                (string) json_encode([
                    'success'      => false,
                    'challenge_ts' => '2024-12-24T03:35:26Z',
                    'hostname'     => 'localhost',
                    'error-codes'  => ['invalid-input-response'],
                ]),
                ['http_code' => 200]
            )
        );

        $config = new VerifyConfiguration(
            new Token('invalid-token')
        );

        $response = $this->turnstile->verify($config);

        self::assertFalse($response->isSuccess());
        self::assertSame(['invalid-input-response'], $response->getErrorCodes());
    }

    public function testMalformedResponse(): void
    {
        $this->mockHttpClient->setResponseFactory(
            new MockResponse(
                'invalid json',
                ['http_code' => 200]
            )
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode Turnstile response');
        $this->turnstile->verify($config);
    }

    public function testNetworkError(): void
    {
        $this->mockHttpClient->setResponseFactory(
            new MockResponse('', [
                'error'     => 'Network error',
                'http_code' => 0,
            ])
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->turnstile->verify($config);
    }

    public function testServerError(): void
    {
        $this->mockHttpClient = new MockHttpClient(
            new MockResponse('Server Error', [
                'http_code'        => 500,
                'response_headers' => ['content-type' => 'text/plain'],
                'error'            => new \RuntimeException('Server Error'),
            ]),
            'https://challenges.cloudflare.com'
        );

        $client = new Psr18Client($this->mockHttpClient);

        $this->turnstile = new Turnstile(
            $client,
            $this->psr17Factory,
            $this->psr17Factory,
            new SecretKey('test-secret-key')
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->turnstile->verify($config);
    }

    public function testSuccessfulVerification(): void
    {
        $this->mockHttpClient->setResponseFactory(
            new MockResponse(
                (string) json_encode([
                    'success'      => true,
                    'challenge_ts' => '2024-12-24T03:35:26Z',
                    'hostname'     => 'localhost',
                    'error-codes'  => [],
                    'action'       => 'test',
                    'cdata'        => 'test-data',
                ]),
                ['http_code' => 200]
            )
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $response = $this->turnstile->verify($config);

        self::assertTrue($response->isSuccess());
        self::assertSame('2024-12-24T03:35:26Z', $response->getChallengeTs());
        self::assertSame('localhost', $response->getHostname());
        self::assertSame('test', $response->getAction());
        self::assertSame('test-data', $response->getCdata());
    }

    public function testTimeoutError(): void
    {
        $this->mockHttpClient->setResponseFactory(
            new MockResponse('', [
                'error'     => 'Timeout error',
                'http_code' => 0,
                'timeout'   => true,
            ])
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->turnstile->verify($config);
    }

    public function testWithCustomData(): void
    {
        $this->mockHttpClient->setResponseFactory(
            new MockResponse(
                (string) json_encode([
                    'success'      => true,
                    'challenge_ts' => '2024-12-24T03:35:26Z',
                    'hostname'     => 'localhost',
                    'error-codes'  => [],
                    'action'       => 'login',
                    'cdata'        => 'user123',
                    'metadata'     => ['key' => 'value'],
                ]),
                ['http_code' => 200]
            )
        );

        $config = new VerifyConfiguration(
            new Token('test-token'),
            null,
            null,
            ['action' => 'login', 'cdata' => 'user123']
        );

        $response = $this->turnstile->verify($config);

        self::assertTrue($response->isSuccess());
        self::assertSame('login', $response->getAction());
        self::assertSame('user123', $response->getCdata());
    }
}
