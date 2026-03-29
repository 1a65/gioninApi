<?php

namespace Gionin\Tests\Http;

use Gionin\Http\RetryClient;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

class RetryClientTest extends TestCase
{
    public function testSuccessfulRequestNoRetry(): void
    {
        $inner = $this->createMock(ClientInterface::class);
        $inner->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(200, [], 'ok'));

        $client = new RetryClient($inner, maxRetries: 3, baseDelayMs: 1);
        $response = $client->sendRequest(new Request('GET', 'https://example.com'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRetriesOnServerError(): void
    {
        $inner = $this->createMock(ClientInterface::class);
        $inner->expects($this->exactly(4))
            ->method('sendRequest')
            ->willReturn(new Response(500, [], 'error'));

        $client = new RetryClient($inner, maxRetries: 3, baseDelayMs: 1);
        $response = $client->sendRequest(new Request('GET', 'https://example.com'));

        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testRetriesOnException(): void
    {
        $inner = $this->createMock(ClientInterface::class);
        $inner->expects($this->exactly(4))
            ->method('sendRequest')
            ->willThrowException(new \RuntimeException('connection failed'));

        $client = new RetryClient($inner, maxRetries: 3, baseDelayMs: 1);

        $this->expectException(\RuntimeException::class);
        $client->sendRequest(new Request('GET', 'https://example.com'));
    }

    public function testRecoverAfterRetry(): void
    {
        $inner = $this->createMock(ClientInterface::class);
        $inner->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls(
                new Response(503, [], 'unavailable'),
                new Response(200, [], 'ok'),
            );

        $client = new RetryClient($inner, maxRetries: 3, baseDelayMs: 1);
        $response = $client->sendRequest(new Request('GET', 'https://example.com'));

        $this->assertEquals(200, $response->getStatusCode());
    }
}
