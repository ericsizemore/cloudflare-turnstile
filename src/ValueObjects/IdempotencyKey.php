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

namespace Esi\CloudflareTurnstile\ValueObjects;

use Stringable;

/**
 * Represents an idempotency key for Turnstile verification requests.
 *
 * This value object helps prevent duplicate verifications by providing a unique identifier for each request.
 * When the same idempotency key is used within a time window, Cloudflare will return the same response instead
 * of performing a new verification.
 *
 * Use cases:
 * - Preventing duplicate form submissions.
 * - Handling network retries safely.
 * - Ensuring verification consistency.
 *
 * @psalm-immutable
 *
 * @final
 */
final readonly class IdempotencyKey implements Stringable
{
    /**
     * Creates a new IdempotencyKey instance.
     *
     * @param string $value A unique identifier for this verification request.
     */
    public function __construct(private string $value) {}

    /**
     * Get the raw idempotency key value.
     *
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
