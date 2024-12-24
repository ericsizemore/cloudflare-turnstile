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
 * @psalm-immutable
 */
final readonly class VerifyConfiguration
{
    /**
     * @param array<string, string> $customData
     */
    public function __construct(
        private Token $token,
        private ?IpAddress $remoteIp = null,
        private ?IdempotencyKey $idempotencyKey = null,
        private array $customData = []
    ) {}

    /**
     * @return array<string, string>
     */
    public function getCustomData(): array
    {
        return $this->customData;
    }

    public function getIdempotencyKey(): ?IdempotencyKey
    {
        return $this->idempotencyKey;
    }

    public function getRemoteIp(): ?IpAddress
    {
        return $this->remoteIp;
    }

    public function getToken(): Token
    {
        return $this->token;
    }
}
