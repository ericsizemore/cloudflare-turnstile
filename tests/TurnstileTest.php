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

namespace Esi\CloudflareTurnstile\Tests;

use Esi\CloudflareTurnstile\Response;
use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * @internal
 */
#[CoversClass(Turnstile::class)]
#[UsesClass(Response::class)]
#[UsesClass(IdempotencyKey::class)]
#[UsesClass(IpAddress::class)]
#[UsesClass(SecretKey::class)]
#[UsesClass(Token::class)]
#[UsesClass(VerifyConfiguration::class)]
final class TurnstileTest extends TestCase
{
    private ClientInterface&MockObject $httpClient;
    private MockObject&RequestFactoryInterface $requestFactory;
    private SecretKey $secretKey;
    private MockObject&StreamFactoryInterface $streamFactory;
    private Turnstile $turnstile;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->httpClient     = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory  = $this->createMock(StreamFactoryInterface::class);
        $this->secretKey      = new SecretKey('test-secret-key');

        $this->turnstile = new Turnstile(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->secretKey
        );
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    #[Test]
    public function verifyFailedResponse(): void
    {
        // Create mocks
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        // Setup failed response data
        $responseData = [
            'success'      => false,
            'challenge_ts' => '2024-12-24T00:12:00Z',
            'hostname'     => 'example.com',
            'error-codes'  => ['invalid-input-response'],
        ];

        // Configure mocks (similar to success test)
        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->willReturn($request);

        $request
            ->expects(self::exactly(2))
            ->method('withHeader')
            ->willReturnSelf();

        $this->streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->willReturn($stream);

        $request
            ->expects(self::once())
            ->method('withBody')
            ->willReturn($request);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($bodyStream);

        $bodyStream
            ->expects(self::once())
            ->method('__toString')
            ->willReturn(json_encode($responseData));

        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->willReturn($response);

        // Create configuration
        $config = new VerifyConfiguration(
            new Token('invalid-token')
        );

        // Perform verification
        $result = $this->turnstile->verify($config);

        // Assertions
        self::assertFalse($result->isSuccess());
        self::assertEquals(['invalid-input-response'], $result->getErrorCodes());
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    #[Test]
    public function verifyInvalidJsonResponse(): void
    {
        // Create mocks
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        // Configure mocks
        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->willReturn($request);

        $request
            ->expects(self::exactly(2))
            ->method('withHeader')
            ->willReturnSelf();

        $this->streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->willReturn($stream);

        $request
            ->expects(self::once())
            ->method('withBody')
            ->willReturn($request);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($bodyStream);

        $bodyStream
            ->expects(self::once())
            ->method('__toString')
            ->willReturn('invalid json');

        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->willReturn($response);

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode Turnstile response');

        $this->turnstile->verify($config);
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    #[Test]
    public function verifySuccessfulResponse(): void
    {
        // Create mocks
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        // Setup successful response data
        $responseData = [
            'success'      => true,
            'challenge_ts' => '2024-12-24T00:12:00Z',
            'hostname'     => 'example.com',
            'error-codes'  => [],
            'action'       => 'login',
            'cdata'        => 'some-data',
        ];

        // Configure mocks
        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify')
            ->willReturn($request);

        $request
            ->expects(self::exactly(2))
            ->method('withHeader')
            ->willReturnSelf();

        $this->streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->willReturn($stream);

        $request
            ->expects(self::once())
            ->method('withBody')
            ->with($stream)
            ->willReturn($request);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($bodyStream);

        $bodyStream
            ->expects(self::once())
            ->method('__toString')
            ->willReturn(json_encode($responseData));

        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        // Create configuration
        $config = new VerifyConfiguration(
            new Token('test-token'),
            new IpAddress('127.0.0.1'),
            new IdempotencyKey('test-idempotency-key'),
            ['custom' => 'data']
        );

        // Perform verification
        $result = $this->turnstile->verify($config);

        // Assertions
        self::assertTrue($result->isSuccess());
        self::assertSame('2024-12-24T00:12:00Z', $result->getChallengeTs());
        self::assertSame('example.com', $result->getHostname());
        self::assertSame([], $result->getErrorCodes());
        self::assertSame('login', $result->getAction());
        self::assertSame('some-data', $result->getCdata());
    }
}
