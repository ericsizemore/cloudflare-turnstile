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

namespace Esi\CloudflareTurnstile\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when a value object receives an invalid value.
 */
final class ValueObjectInvalidValueException extends InvalidArgumentException
{
    /**
     * Create exception for invalid IP address.
     *
     * @psalm-pure
     */
    public static function invalidIpAddress(): self
    {
        return new self('Invalid IP address.');
    }

    /**
     * Create exception for invalid secret key.
     *
     * @psalm-pure
     */
    public static function invalidSecretKey(): self
    {
        return new self('Invalid secret key: cannot be empty.');
    }

    /**
     * Create exception for invalid token.
     *
     * @psalm-pure
     */
    public static function invalidToken(): self
    {
        return new self('Invalid token: cannot be empty.');
    }
}
