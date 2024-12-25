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

use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\Token;

/**
 * Configuration for Turnstile verification requests.
 *
 * This class holds all parameters that can be sent to Cloudflare's verification API.
 *
 * @psalm-immutable
 *
 * @final
 */
final readonly class VerifyConfiguration
{
    /**
     * Creates a new verification configuration.
     *
     * @param Token                 $token          The response token from the Turnstile widget.
     * @param null|IpAddress        $remoteIp       Optional. The user's IP address.
     * @param null|IdempotencyKey   $idempotencyKey Optional. Key to prevent duplicate requests.
     * @param array<string, string> $customData     Optional. Additional data to include in the request.
     */
    public function __construct(
        private Token $token,
        private ?IpAddress $remoteIp = null,
        private ?IdempotencyKey $idempotencyKey = null,
        private array $customData = []
    ) {}

    /**
     * Gets the custom data (cdata) passed to the widget on the client side.
     *
     * @return array<string, string> The custom data.
     */
    public function getCustomData(): array
    {
        return $this->customData;
    }

    /**
     * Gets the UUID to be associated with the response, if one was provided.
     *
     * @return null|IdempotencyKey The Idempotency Key (UUID).
     */
    public function getIdempotencyKey(): ?IdempotencyKey
    {
        return $this->idempotencyKey;
    }

    /**
     * Gets the visitor's IP address, if one was provided.
     *
     * @return null|IpAddress The IP address.
     */
    public function getRemoteIp(): ?IpAddress
    {
        return $this->remoteIp;
    }

    /**
     * Gets the response provided by the Turnstile client-side render on your site.
     *
     * @return Token The Turnstile render response.
     */
    public function getToken(): Token
    {
        return $this->token;
    }
}
