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
use Esi\CloudflareTurnstile\ValueObjects\Token;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(Token::class)]
#[CoversClass(ValueObjectInvalidValueException::class)]
final class TokenTest extends TestCase
{
    #[Test]
    public function emptyTokenThrowsException(): void
    {
        $this->expectException(ValueObjectInvalidValueException::class);
        $this->expectExceptionMessage('Invalid token: cannot be empty.');
        new Token('');
    }

    #[Test]
    public function validTokenCreation(): void
    {
        $token = new Token('valid-token');
        self::assertSame('valid-token', (string) $token);
    }
}
