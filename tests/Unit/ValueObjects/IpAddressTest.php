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

use Esi\CloudflareTurnstile\ValueObjects\IpAddress;
use Generator;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(IpAddress::class)]
final class IpAddressTest extends TestCase
{
    #[Test]
    #[DataProvider('invalidIpAddresses')]
    public function invalidIpAddressCreation(string $ip): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpAddress($ip);
    }

    #[Test]
    #[DataProvider('validIpAddresses')]
    public function validIpAddressCreation(string $ip): void
    {
        $ipAddress = new IpAddress($ip);
        self::assertSame($ip, (string) $ipAddress);
    }

    /**
     * @return Generator<int, list<string>>
     */
    public static function invalidIpAddresses(): Generator
    {
        yield ['256.256.256.256'];
        yield ['not.an.ip.address'];
        yield ['127.0.0'];
        yield [''];
    }

    /**
     * @return Generator<int, list<string>>
     */
    public static function validIpAddresses(): Generator
    {
        yield ['127.0.0.1'];
        yield ['192.168.1.1'];
        yield ['::1'];
        yield ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'];
    }
}
