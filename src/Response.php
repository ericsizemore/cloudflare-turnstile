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
 */
/**
 * Represents a Cloudflare Turnstile verification response.
 *
 * This immutable class contains all possible fields returned by the Cloudflare verification API.
 *
 * @psalm-immutable
 *
 * @psalm-type RawDataArray = array{
 *     success: bool,
 *     challenge_ts: string,
 *     hostname: string,
 *     error-codes: string[],
 *     action?: string,
 *     cdata?: string,
 *     metadata?: string[]
 * }
 *
 * @final
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
     * Creates a new Response instance from raw API data.
     *
     * @param RawDataArray $data Raw response data from the Cloudflare API.
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

    /**
     * Gets the optional action specified during widget rendering.
     *
     * This value is only present if an action was specified in the widget configuration.
     * When used, the action parameter helps differentiate widgets using the same sitekey
     * in analytics. Its integrity is protected against modifications from an attacker.
     *
     * Example usage when expecting a specific action:
     * <code>
     * $response = $turnstile->verify($config);
     *
     * if ($response->isSuccess()) {
     *     // Only validate action if your implementation expects one
     *     if ($response->getAction() !== null && $response->getAction() === 'my_action') {
     *         // Verification successful and action matches
     *         echo 'Challenge passed!';
     *     } else {
     *         // Action mismatch or not present
     *         echo 'Challenge failed! Action mismatch.';
     *     }
     * } else {
     *     // Verification failed
     *     echo 'Challenge failed: ' . implode(', ', $response->getErrorCodes());
     * }
     * </code>
     *
     * @see https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configurations
     *
     * @return null|string The action name if specified, null otherwise.
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * Gets the optional custom data provided during verification.
     *
     * @return null|string The custom data if provided, null otherwise.
     */
    public function getCdata(): ?string
    {
        return $this->cdata;
    }

    /**
     * Gets the challenge timestamp for the time the challenge was solved.
     *
     * @return string The challenge timestamp in ISO format.
     */
    public function getChallengeTs(): string
    {
        return $this->challengeTs;
    }

    /**
     * Gets the error codes returned by the API, if any.
     *
     * @return array<string> An array of error codes, if there were errors.
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }

    /**
     * Gets the hostname for which the challenge was served.
     *
     * @return string The hostname.
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * Enterprise only.
     *
     * Gets the Ephemeral ID in siteverify.
     *
     * @see https://developers.cloudflare.com/turnstile/concepts/ephemeral-id/
     *
     * @return array{}|string[] The metadata.
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Gets the raw data as passed when instantiated Response.
     *
     * @return RawDataArray
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Gets the success status of the verify request.
     *
     * @return bool True on success, false on failure.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}
