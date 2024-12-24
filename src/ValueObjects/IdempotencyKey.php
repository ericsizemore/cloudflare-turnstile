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
 * @psalm-immutable
 */
final readonly class IdempotencyKey implements Stringable
{
    public function __construct(private string $value) {}

    public function __toString(): string
    {
        return $this->value;
    }
}
