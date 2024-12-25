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

namespace Esi\CloudflareTurnstile;

use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

use function http_build_query;
use function json_decode;
use function json_last_error;

use const JSON_ERROR_NONE;

/**
 * Cloudflare Turnstile verification client.
 *
 * This class handles server-side validation of Cloudflare Turnstile challenges.
 * It requires a PSR-18 compatible HTTP client and PSR-17 factories.
 *
 * Example usage:
 * <code>
 * $client = new Turnstile(
 *     $httpClient,
 *     $requestFactory,
 *     $streamFactory,
 *     new SecretKey('your-secret-key')
 * );
 *
 * $config = new VerifyConfiguration(
 *     new Token($_POST['cf-turnstile-response']),
 *     new IpAddress($_SERVER['REMOTE_ADDR'])
 * );
 *
 * try {
 *     $response = $client->verify($config);
 *
 *     if ($response->isSuccess()) {
 *         // Verification successful
 *     }
 * } catch (ClientExceptionInterface $e) {
 *     // Handle HTTP client errors
 * }
 * </code>
 *
 * @see https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
 * @see https://github.com/ericsizemore/cloudflare-turnstile/blob/main/docs/faq.md#related-documentation
 *
 * @phpstan-import-type RawDataArray from Response
 *
 * @final
 */
final readonly class Turnstile
{
    /**
     * The Cloudflare Turnstile verification endpoint.
     *
     * @var string
     */
    private const VerifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Creates a new Turnstile verification client.
     *
     * @param ClientInterface         $httpClient     PSR-18 compatible HTTP client.
     * @param RequestFactoryInterface $requestFactory PSR-17 compatible request factory.
     * @param StreamFactoryInterface  $streamFactory  PSR-17 compatible stream factory.
     * @param SecretKey               $secretKey      Your Cloudflare Turnstile secret key.
     */
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private SecretKey $secretKey
    ) {}

    /**
     * Verifies a Turnstile response token.
     *
     * Sends a verification request to Cloudflare's API to validate the challenge response.
     *
     * @param VerifyConfiguration $config Configuration containing the token and optional parameters.
     *
     * @throws RuntimeException         If the response cannot be decoded.
     * @throws ClientExceptionInterface If the HTTP client encounters an error.
     *
     * @return Response The verification response from Cloudflare.
     *
     * @see https://developers.cloudflare.com/turnstile/get-started/server-side-validation/#verification-api
     */
    public function verify(VerifyConfiguration $config): Response
    {
        $params = [
            'secret'   => (string) $this->secretKey,
            'response' => (string) $config->getToken(),
        ];

        if ($config->getRemoteIp() instanceof ValueObjects\IpAddress) {
            $params['remoteip'] = (string) $config->getRemoteIp();
        }

        if ($config->getIdempotencyKey() instanceof ValueObjects\IdempotencyKey) {
            $params['idempotency_key'] = (string) $config->getIdempotencyKey();
        }

        foreach ($config->getCustomData() as $key => $value) {
            $params[$key] = $value;
        }

        $request = $this->requestFactory->createRequest('POST', self::VerifyUrl)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Accept', 'application/json');

        $body    = $this->streamFactory->createStream(http_build_query($params));
        $request = $request->withBody($body);

        $response = $this->httpClient->sendRequest($request);

        $result = json_decode((string) $response->getBody(), true);

        if (!\is_array($result) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode Turnstile response');
        }

        /**
         * @var RawDataArray $result
         */
        return new Response($result);
    }
}
