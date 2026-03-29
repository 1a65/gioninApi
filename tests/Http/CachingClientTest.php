<?php

namespace Gionin\Tests\Http;

use Gionin\Http\CachingClient;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;

class CachingClientTest extends TestCase
{
    public function testCachesGetRequests(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);
        $cache->expects($this->once())->method('set');

        $inner = $this->createMock(ClientInterface::class);
        $inner->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(200, [], '{"data":1}'));

        $client = new CachingClient($inner, $cache);
        $client->sendRequest(new Request('GET', 'https://example.com/test'));
    }

    public function testReturnsCachedResponse(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn([
            'statusCode' => 200,
            'headers' => [],
            'body' => '{"cached":true}',
        ]);

        $inner = $this->createMock(ClientInterface::class);
        $inner->expects($this->never())->method('sendRequest');

        $client = new CachingClient($inner, $cache);
        $response = $client->sendRequest(new Request('GET', 'https://example.com/test'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('cached', (string) $response->getBody());
    }

    public function testDoesNotCachePostRequests(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->never())->method('set');
        $cache->expects($this->never())->method('get');

        $inner = $this->createMock(ClientInterface::class);
        $inner->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(200, [], '{}'));

        $client = new CachingClient($inner, $cache);
        $client->sendRequest(new Request('POST', 'https://example.com/test'));
    }
}
