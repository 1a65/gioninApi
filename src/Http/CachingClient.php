<?php

namespace Gionin\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Nyholm\Psr7\Response;

class CachingClient implements ClientInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly CacheInterface $cache,
        private readonly int $defaultTtl = 300,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'GET') {
            return $this->client->sendRequest($request);
        }

        $key = $this->buildCacheKey($request);
        $cached = $this->cache->get($key);

        if ($cached !== null) {
            return new Response(
                $cached['statusCode'],
                $cached['headers'],
                $cached['body'],
            );
        }

        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->cache->set($key, [
                'statusCode' => $response->getStatusCode(),
                'headers'    => $response->getHeaders(),
                'body'       => (string) $response->getBody(),
            ], $this->defaultTtl);
        }

        return $response;
    }

    private function buildCacheKey(RequestInterface $request): string
    {
        return 'gionin_' . hash('sha256', $request->getMethod() . '|' . (string) $request->getUri());
    }
}
