<?php

namespace Gionin\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RateLimitedClient implements ClientInterface
{
    private float $tokens;
    private float $lastRefill;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly float $maxTokens = 10.0,
        private readonly float $refillRate = 10.0,
    ) {
        $this->tokens = $this->maxTokens;
        $this->lastRefill = microtime(true);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->waitForToken();
        return $this->client->sendRequest($request);
    }

    private function waitForToken(): void
    {
        $this->refill();

        while ($this->tokens < 1.0) {
            $waitTime = (1.0 - $this->tokens) / $this->refillRate;
            usleep((int) ($waitTime * 1_000_000));
            $this->refill();
        }

        $this->tokens -= 1.0;
    }

    private function refill(): void
    {
        $now = microtime(true);
        $elapsed = $now - $this->lastRefill;
        $this->tokens = min($this->maxTokens, $this->tokens + ($elapsed * $this->refillRate));
        $this->lastRefill = $now;
    }
}
