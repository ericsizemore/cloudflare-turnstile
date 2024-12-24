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

namespace Esi\CloudflareTurnstile;

/**
 * @psalm-immutable
 *
 * @psalm-type RawDataArray = array{
 *      success: bool,
 *      challenge_ts: string,
 *      hostname: string,
 *      error-codes: string[],
 *      action?: string,
 *      cdata?: string,
 *      metadata?: string[]
 *  }
 */
final readonly class Response
{
    private ?string $action;

    private ?string $cdata;

    private string $challengeTs;

    /** @var array<string> */
    private array $errorCodes;

    private string $hostname;

    /**
     * Note: Enterprise Only.
     *
     * @var array{}|string[]
     */
    private array $metadata;

    private bool $success;

    /**
     * @param RawDataArray $data
     */
    public function __construct(private array $data)
    {
        $this->success     = $data['success'];
        $this->challengeTs = $data['challenge_ts'];
        $this->hostname    = $data['hostname'];
        $this->errorCodes  = $data['error-codes'];
        $this->action      = $data['action'] ?? null;
        $this->cdata       = $data['cdata'] ?? null;
        $this->metadata    = $data['metadata'] ?? [];
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getCdata(): ?string
    {
        return $this->cdata;
    }

    public function getChallengeTs(): string
    {
        return $this->challengeTs;
    }

    /**
     * @return array<string>
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @return array{}|string[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return RawDataArray
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
