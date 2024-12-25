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

final class ValueObjectInvalidValueException extends InvalidArgumentException
{
    public static function invalidIpAddress(): ValueObjectInvalidValueException
    {
        return new self('Invalid IP address.');
    }

    public static function invalidSecretKey(): ValueObjectInvalidValueException
    {
        return new self('Invalid secret key: cannot be empty.');
    }

    public static function invalidToken(): ValueObjectInvalidValueException
    {
        return new self('Invalid token: cannot be empty.');
    }
}
