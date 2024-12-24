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

use InvalidArgumentException;
use Stringable;

use function filter_var;

/**
 * @psalm-immutable
 */
final readonly class IpAddress implements Stringable
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(private string $value)
    {
        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException('Invalid IP address');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
