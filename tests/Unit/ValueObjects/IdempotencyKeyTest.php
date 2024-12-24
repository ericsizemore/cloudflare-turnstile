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

namespace Esi\CloudflareTurnstile\Tests\Unit\ValueObjects;

use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(IdempotencyKey::class)]
final class IdempotencyKeyTest extends TestCase
{
    #[Test]
    public function emptyIdempotencyKeyIsAllowed(): void
    {
        $key = new IdempotencyKey('');
        self::assertSame('', (string) $key);
    }

    #[Test]
    public function validIdempotencyKeyCreation(): void
    {
        $key = new IdempotencyKey('test-key');
        self::assertSame('test-key', (string) $key);
    }
}
