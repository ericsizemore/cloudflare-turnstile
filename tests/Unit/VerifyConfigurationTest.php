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

namespace Esi\CloudflareTurnstile\Tests\Unit;

use Esi\CloudflareTurnstile\ValueObjects\IdempotencyKey;
use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Esi\CloudflareTurnstile\ValueObjects\Token;
use Esi\CloudflareTurnstile\VerifyConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(VerifyConfiguration::class)]
#[CoversClass(IdempotencyKey::class)]
#[CoversClass(IpAddress::class)]
#[CoversClass(Token::class)]
final class VerifyConfigurationTest extends TestCase
{
    #[Test]
    public function createFullConfiguration(): void
    {
        $token          = new Token('test-token');
        $ipAddress      = new IpAddress('127.0.0.1');
        $idempotencyKey = new IdempotencyKey('test-key');
        $customData     = ['key' => 'value'];

        $config = new VerifyConfiguration(
            $token,
            $ipAddress,
            $idempotencyKey,
            $customData
        );

        self::assertSame($token, $config->getToken());
        self::assertSame($ipAddress, $config->getRemoteIp());
        self::assertSame($idempotencyKey, $config->getIdempotencyKey());
        self::assertSame($customData, $config->getCustomData());
    }
    #[Test]
    public function createMinimalConfiguration(): void
    {
        $token  = new Token('test-token');
        $config = new VerifyConfiguration($token);

        self::assertSame($token, $config->getToken());
        self::assertNull($config->getRemoteIp());
        self::assertNull($config->getIdempotencyKey());
        self::assertSame([], $config->getCustomData());
    }
}
