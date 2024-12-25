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

use Esi\CloudflareTurnstile\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-api
 */
#[CoversClass(Response::class)]
final class ResponseTest extends TestCase
{
    #[Test]
    public function createFailedResponse(): void
    {
        $data = [
            'success'      => false,
            'challenge_ts' => '2024-12-24T00:12:00Z',
            'hostname'     => 'example.com',
            'error-codes'  => ['invalid-input-response'],
        ];

        $response = new Response($data);

        self::assertFalse($response->isSuccess());
        self::assertNull($response->getAction());
        self::assertNull($response->getCdata());
        self::assertSame([], $response->getMetadata());
    }

    #[Test]
    public function createSuccessfulResponse(): void
    {
        $data = [
            'success'      => true,
            'challenge_ts' => '2024-12-24T00:12:00Z',
            'hostname'     => 'example.com',
            'error-codes'  => [],
            'action'       => 'login',
            'cdata'        => 'custom-data',
            'metadata'     => ['key' => 'value'],
        ];

        $response = new Response($data);

        self::assertTrue($response->isSuccess());
        self::assertSame('2024-12-24T00:12:00Z', $response->getChallengeTs());
        self::assertSame('example.com', $response->getHostname());
        self::assertSame([], $response->getErrorCodes());
        self::assertSame('login', $response->getAction());
        self::assertSame('custom-data', $response->getCdata());
        self::assertSame(['key' => 'value'], $response->getMetadata());
        self::assertSame($data, $response->getRawData());
    }

    #[Test]
    public function responseHandlesAllFields(): void
    {
        $data = [
            'success'      => true,
            'challenge_ts' => '2024-12-24T00:00:00Z',
            'hostname'     => 'test.com',
            'error-codes'  => [],
            'action'       => 'test',
            'cdata'        => 'custom',
            'metadata'     => ['key' => 'value'],
        ];

        $response = new Response($data);

        self::assertSame('test', $response->getAction());
        self::assertSame('custom', $response->getCdata());
        self::assertSame(['key' => 'value'], $response->getMetadata());
        self::assertSame($data, $response->getRawData());
    }

    #[Test]
    public function responseHandlesAllNullableFields(): void
    {
        $data = [
            'success'      => true,
            'challenge_ts' => '2024-12-24T00:00:00Z',
            'hostname'     => 'test.com',
            'error-codes'  => [],
        ];

        $response = new Response($data);

        self::assertNull($response->getAction());
        self::assertNull($response->getCdata());
        self::assertSame([], $response->getMetadata());
    }

    #[Test]
    public function responseHandlesErrorCodesCorrectly(): void
    {
        $data = [
            'success'      => false,
            'challenge_ts' => '2024-12-24T00:12:00Z',
            'hostname'     => 'example.com',
            'error-codes'  => ['error1', 'error2'],
        ];

        $response = new Response($data);

        self::assertFalse($response->isSuccess());
        self::assertSame(['error1', 'error2'], $response->getErrorCodes());
    }
}
