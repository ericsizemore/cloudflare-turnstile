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

use Esi\CloudflareTurnstile\Exceptions\ValueObjectInvalidValueException;
use Esi\CloudflareTurnstile\ValueObjects\SecretKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(SecretKey::class)]
#[CoversClass(ValueObjectInvalidValueException::class)]
final class SecretKeyTest extends TestCase
{
    #[Test]
    public function emptySecretKeyThrowsException(): void
    {
        $this->expectException(ValueObjectInvalidValueException::class);
        $this->expectExceptionMessage('Invalid secret key: cannot be empty.');
        new SecretKey('');
    }

    #[Test]
    public function validSecretKeyCreation(): void
    {
        $secretKey = new SecretKey('valid-secret-key');
        self::assertSame('valid-secret-key', (string) $secretKey);
    }
}
