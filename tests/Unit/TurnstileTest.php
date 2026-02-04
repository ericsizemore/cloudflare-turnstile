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

namespace Esi\CloudflareTurnstile\Tests\Unit;

use Esi\CloudflareTurnstile\Response;
use Esi\CloudflareTurnstile\Turnstile;
use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
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
#[AllowMockObjectsWithoutExpectations]
final class TurnstileTest extends TestCase
{
    private ClientInterface&MockObject $httpClient;

    private MockObject&RequestFactoryInterface $requestFactory;

    private MockObject&StreamFactoryInterface $streamFactory;

    private Turnstile $turnstile;

    /**
     * @throws Exception
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->httpClient     = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory  = $this->createMock(StreamFactoryInterface::class);
        $secretKey            = new SecretKey('test-secret-key');

        $this->turnstile = new Turnstile(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $secretKey
        );
    }

    #[Test]
    public function verifyEnsuresProperStringCasting(): void
    {
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $capturedParams = null;
        $this->streamFactory->method('createStream')
            ->willReturnCallback(static function (string $content) use ($stream, &$capturedParams): MockObject&StreamInterface {
                $capturedParams = $content;
                return $stream;
            });

        $this->setupMocks($request, $response, $stream, $bodyStream);

        $config = new VerifyConfiguration(
            new Token('test-token'),
            new IpAddress('127.0.0.1'),
            new IdempotencyKey('test-key')
        );

        $this->turnstile->verify($config);

        $params = [];

        self::assertNotNull($capturedParams);
        parse_str($capturedParams, $params);
        self::assertSame('test-token', $params['response']);
        self::assertSame('127.0.0.1', $params['remoteip']);
        self::assertSame('test-key', $params['idempotency_key']);
    }

    #[Test]
    public function verifyEnsuresSecretKeyIsPresent(): void
    {
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $capturedParams = null;
        $this->streamFactory->method('createStream')
            ->willReturnCallback(static function (string $content) use ($stream, &$capturedParams): MockObject&StreamInterface {
                $capturedParams = $content;
                return $stream;
            });

        $this->setupMocks($request, $response, $stream, $bodyStream);

        $config = new VerifyConfiguration(new Token('test-token'));
        $this->turnstile->verify($config);

        $params = [];
        self::assertNotNull($capturedParams);
        parse_str($capturedParams, $params);
        self::assertArrayHasKey('secret', $params);
        self::assertSame('test-secret-key', $params['secret']);
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
        self::assertSame(['invalid-input-response'], $result->getErrorCodes());
    }

    #[Test]
    public function verifyHandlesClientException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $stream  = $this->createMock(StreamInterface::class);

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

        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->willThrowException(new class () extends \Exception implements ClientExceptionInterface {});

        $config = new VerifyConfiguration(
            new Token('test-token')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->turnstile->verify($config);
    }

    #[Test]
    public function verifyHandlesCustomDataCorrectly(): void
    {
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $capturedParams = null;
        $this->streamFactory->method('createStream')
            ->willReturnCallback(static function (string $content) use ($stream, &$capturedParams): MockObject&StreamInterface {
                $capturedParams = $content;
                return $stream;
            });

        $this->setupMocks($request, $response, $stream, $bodyStream);

        $config = new VerifyConfiguration(
            new Token('test-token'),
            null,
            null,
            ['custom_key' => 'custom_value']
        );

        $this->turnstile->verify($config);

        $params = [];

        self::assertNotNull($capturedParams);
        parse_str($capturedParams, $params);
        self::assertSame('custom_value', $params['custom_key']);
    }

    #[Test]
    public function verifyHandlesInstanceOfChecksCorrectly(): void
    {
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $capturedParams = null;
        $this->streamFactory->method('createStream')
            ->willReturnCallback(static function (string $content) use ($stream, &$capturedParams): MockObject&StreamInterface {
                $capturedParams = $content;
                return $stream;
            });

        $this->setupMocks($request, $response, $stream, $bodyStream);

        // Test without optional parameters
        $configWithoutOptionals = new VerifyConfiguration(new Token('test-token'));
        $this->turnstile->verify($configWithoutOptionals);

        $params = [];
        self::assertNotNull($capturedParams);
        parse_str($capturedParams, $params);
        self::assertArrayNotHasKey('remoteip', $params);
        self::assertArrayNotHasKey('idempotency_key', $params);

        // Test with optional parameters
        $configWithOptionals = new VerifyConfiguration(
            new Token('test-token'),
            new IpAddress('127.0.0.1'),
            new IdempotencyKey('test-key')
        );

        $this->turnstile->verify($configWithOptionals);

        $params = [];

        parse_str($capturedParams, $params);
        self::assertArrayHasKey('remoteip', $params);
        self::assertArrayHasKey('idempotency_key', $params);
    }

    #[Test]
    public function verifyHandlesInvalidJsonResponse(): void
    {
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $bodyStream->method('__toString')->willReturn('null'); // This will decode to null, not an array

        $this->setupMocks($request, $response, $stream, $bodyStream);

        $config = new VerifyConfiguration(new Token('test-token'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode Turnstile response');

        $this->turnstile->verify($config);
    }

    #[Test]
    public function verifyHandlesJsonErrorsCorrectly(): void
    {
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $bodyStream->method('__toString')->willReturn('invalid json');

        $this->setupMocks($request, $response, $stream, $bodyStream);

        $config = new VerifyConfiguration(new Token('test-token'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode Turnstile response');

        $this->turnstile->verify($config);
    }

    #[Test]
    public function verifyHandlesJsonSyntaxError(): void
    {
        $request    = $this->createMock(RequestInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $stream     = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $bodyStream->method('__toString')->willReturn('{invalid json}'); // This will cause a JSON syntax error

        $this->setupMocks($request, $response, $stream, $bodyStream);

        $config = new VerifyConfiguration(new Token('test-token'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode Turnstile response');

        $this->turnstile->verify($config);
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

    private function setupMocks(
        MockObject&RequestInterface $request,
        MockObject&ResponseInterface $response,
        MockObject&StreamInterface $stream,
        MockObject&StreamInterface $bodyStream
    ): void {
        $capturedBody = '';

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->streamFactory->method('createStream')
            ->willReturnCallback(static function (string $content) use ($stream, &$capturedBody): MockObject&StreamInterface {
                $capturedBody = $content;
                $stream->method('__toString')->willReturn($capturedBody);
                return $stream;
            });

        $request->method('withBody')->willReturn($request);
        $response->method('getBody')->willReturn($bodyStream);
        $this->httpClient->method('sendRequest')->willReturn($response);

        $successResponse = [
            'success'      => true,
            'challenge_ts' => '2024-12-24T00:00:00Z',
            'hostname'     => 'test.com',
            'error-codes'  => [],
        ];

        $bodyStream->method('__toString')->willReturn(json_encode($successResponse));
    }
}
