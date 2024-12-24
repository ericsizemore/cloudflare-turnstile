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

use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(Turnstile::class)]
#[CoversClass(\Esi\CloudflareTurnstile\Response::class)]
#[CoversClass(IdempotencyKey::class)]
#[CoversClass(IpAddress::class)]
#[CoversClass(SecretKey::class)]
#[CoversClass(Token::class)]
#[CoversClass(VerifyConfiguration::class)]
final class GuzzleIntegrationTest extends TestCase
{
    private HttpFactory $factory;

    private MockHandler $mockHandler;

    private Turnstile $turnstile;
    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack      = HandlerStack::create($this->mockHandler);

        $client = new Client([
            'handler'     => $handlerStack,
            'http_errors' => true,
        ]);

        $this->factory = new HttpFactory();

        $this->turnstile = new Turnstile(
            $client,
            $this->factory,
            $this->factory,
            new SecretKey('test-secret-key')
        );
    }
    public function testDnsError(): void
    {
        $this->mockHandler->append(
            new ConnectException(
                'Could not resolve host',
                new Request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
                null,
                ['errno' => CURLE_COULDNT_RESOLVE_HOST]
            )
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('Could not resolve host');

        $this->turnstile->verify($config);
    }
    public function testFailedVerification(): void
    {
        // Mock a failed response
        $this->mockHandler->append(
            new Response(200, [], (string) json_encode([
                'success'      => false,
                'challenge_ts' => '2024-12-24T03:23:02Z',
                'hostname'     => 'localhost',
                'error-codes'  => ['invalid-input-response'],
            ]))
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
        // Mock an invalid JSON response
        $this->mockHandler->append(
            new Response(200, [], 'invalid json')
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
        // Mock a network error using ConnectException
        $this->mockHandler->append(
            new ConnectException(
                'Network error',
                new Request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify')
            )
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('Network error');

        $this->turnstile->verify($config);
    }
    public function testRequestFormat(): void
    {
        $this->mockHandler->append(
            new Response(200, [], (string) json_encode([
                'success'      => true,
                'challenge_ts' => '2024-12-24T03:23:02Z',
                'hostname'     => 'localhost',
                'error-codes'  => [],
            ]))
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->turnstile->verify($config);

        $lastRequest = $this->mockHandler->getLastRequest();
        self::assertNotNull($lastRequest);
        self::assertSame('POST', $lastRequest->getMethod());
        self::assertSame(
            'application/x-www-form-urlencoded',
            $lastRequest->getHeaderLine('Content-Type')
        );
        self::assertSame(
            'application/json',
            $lastRequest->getHeaderLine('Accept')
        );
    }
    public function testSuccessfulVerification(): void
    {
        // Mock a successful response
        $this->mockHandler->append(
            new Response(200, [], (string) json_encode([
                'success'      => true,
                'challenge_ts' => '2024-12-24T03:23:02Z',
                'hostname'     => 'localhost',
                'error-codes'  => [],
                'action'       => 'test',
                'cdata'        => 'test-data',
            ]))
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $response = $this->turnstile->verify($config);

        self::assertTrue($response->isSuccess());
        self::assertSame('2024-12-24T03:23:02Z', $response->getChallengeTs());
        self::assertSame('localhost', $response->getHostname());
        self::assertSame('test', $response->getAction());
        self::assertSame('test-data', $response->getCdata());
    }
    public function testTimeoutError(): void
    {
        $this->mockHandler->append(
            new ConnectException(
                'Connection timed out',
                new Request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
                null,
                ['errno' => CURLE_OPERATION_TIMEOUTED]
            )
        );

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage('Connection timed out');

        $this->turnstile->verify($config);
    }
    public function testWithCustomData(): void
    {
        // Mock a successful response with custom data
        $this->mockHandler->append(
            new Response(200, [], (string) json_encode([
                'success'      => true,
                'challenge_ts' => '2024-12-24T03:23:02Z',
                'hostname'     => 'localhost',
                'error-codes'  => [],
                'action'       => 'login',
                'cdata'        => 'user123',
                'metadata'     => ['key' => 'value'],
            ]))
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
