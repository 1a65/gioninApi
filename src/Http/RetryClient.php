<?php

namespace Gionin\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RetryClient implements ClientInterface
{
    private const RETRYABLE_STATUS_CODES = [429, 500, 502, 503];

    public function __construct(
        private readonly ClientInterface $client,
        private readonly int $maxRetries = 3,
        private readonly int $baseDelayMs = 1000,
        private readonly float $multiplier = 2.0,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->maxRetries) {
            try {
                $response = $this->client->sendRequest($request);

                if ($attempt < $this->maxRetries && in_array($response->getStatusCode(), self::RETRYABLE_STATUS_CODES)) {
                    $this->sleep($attempt, $response);
                    $attempt++;
                    continue;
                }

                return $response;
            } catch (\Throwable $e) {
                $lastException = $e;
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                $this->sleep($attempt);
                $attempt++;
            }
        }

        throw $lastException;
    }

    private function sleep(int $attempt, ?ResponseInterface $response = null): void
    {
        $delayMs = (int) ($this->baseDelayMs * ($this->multiplier ** $attempt));

        if ($response !== null && $response->hasHeader('Retry-After')) {
            $retryAfter = $response->getHeaderLine('Retry-After');
            if (is_numeric($retryAfter)) {
                $delayMs = (int) $retryAfter * 1000;
            }
        }

        // Add jitter (0-25%)
        $jitter = (int) ($delayMs * mt_rand(0, 25) / 100);
        usleep(($delayMs + $jitter) * 1000);
    }
}
