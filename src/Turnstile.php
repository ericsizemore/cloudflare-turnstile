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
use function json_last_error_msg;

use const JSON_ERROR_NONE;

/**
 * Cloudflare Turnstile verification client.
 *
 * @see https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
 *
 * @phpstan-import-type RawDataArray from Response
 */
final readonly class Turnstile
{
    private const VerifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private SecretKey $secretKey
    ) {}

    /**
     * Verify a Turnstile response token.
     *
     * @throws RuntimeException         If the request fails or returns invalid JSON
     * @throws ClientExceptionInterface If the HTTP client encounters an error
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
            throw new RuntimeException(\sprintf('Failed to decode Turnstile response, jSON error: %s', json_last_error_msg()));
        }

        /**
         * @var RawDataArray $result
         */
        return new Response($result);
    }
}
