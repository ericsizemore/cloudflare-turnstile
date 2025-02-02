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
use Stringable;

/**
 * Represents a Turnstile response token from the client-side widget.
 *
 * This value object ensures the token is not empty and maintains immutability.
 * The token is provided by the Turnstile widget in the 'cf-turnstile-response' form field.
 *
 * @psalm-immutable
 *
 * @final
 */
final readonly class Token implements Stringable
{
    /**
     * Creates a new Token instance.
     *
     * @param string $value The token value from the Turnstile widget.
     *
     * @throws ValueObjectInvalidValueException If the token is empty.
     */
    public function __construct(private string $value)
    {
        if ($value === '') {
            throw ValueObjectInvalidValueException::invalidToken();
        }
    }

    /**
     * Get the raw token value.
     *
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
