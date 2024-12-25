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

use Esi\CloudflareTurnstile\Exceptions\ValueObjectInvalidValueException;
use SensitiveParameter;
use Stringable;

/**
 * Represents a Cloudflare Turnstile secret key.
 *
 * @psalm-immutable
 */
final readonly class SecretKey implements Stringable
{
    /**
     * @throws ValueObjectInvalidValueException If the secret key is empty or invalid.
     */
    public function __construct(#[SensitiveParameter] private string $value)
    {
        if ($value === '') {
            throw ValueObjectInvalidValueException::invalidSecretKey();
        }
    }

    /**
     * Get the secret key value.
     *
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
