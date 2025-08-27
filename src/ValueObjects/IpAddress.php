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

use Esi\CloudflareTurnstile\Exceptions\ValueObjectInvalidValueException;
use Override;
use Stringable;

use function filter_var;

/**
 * Represents an IP address used in Turnstile verification requests.
 *
 * This value object ensures a valid IP address format (both IPv4 and IPv6 are supported) and maintains
 * immutability. When provided in verification requests, this helps Cloudflare correlate challenges
 * with specific IP addresses.
 *
 * Example valid formats:
 * - IPv4: "192.0.2.1"
 * - IPv6: "2001:db8::1"
 *
 * @psalm-immutable
 *
 * @final
 */
final readonly class IpAddress implements Stringable
{
    /**
     * Creates a new IpAddress instance.
     *
     * @param string $value The IP address to validate.
     *
     * @throws ValueObjectInvalidValueException If the IP address format is invalid.
     */
    public function __construct(private string $value)
    {
        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            throw ValueObjectInvalidValueException::invalidIpAddress();
        }
    }

    /**
     * Get the raw IP address value.
     *
     * @inheritDoc
     */
    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
