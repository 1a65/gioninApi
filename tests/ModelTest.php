<?php

namespace Gionin\Tests;

use Gionin\Exception\ValidationException;
use Gionin\Model;
use Gionin\Response\ApiResponse;
use Gionin\Response\PaginatedResponse;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

class ModelTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $model = new Model(
            user: 'testuser',
            appUsername: 'appuser',
            appSecret: 'secret',
            app: 'myapp',
            table: 'mytable',
        );

        $this->assertEquals('testuser', $this->getProperty($model, '_user'));
        $this->assertEquals('appuser', $this->getProperty($model, '_authUser'));
        $this->assertEquals('secret', $this->getProperty($model, '_authKey'));
        $this->assertEquals('myapp', $this->getProperty($model, 'app'));
        $this->assertEquals('mytable', $this->getProperty($model, 'table'));
    }

    public function testConstructorDefaults(): void
    {
        $model = new Model();

        $this->assertEquals('', $this->getProperty($model, '_user'));
        $this->assertEquals('', $this->getProperty($model, 'app'));
        $this->assertEquals('', $this->getProperty($model, 'table'));
    }

    public function testReset(): void
    {
        $model = new Model();
        $model->reset();

        $this->assertEquals([], $this->getProperty($model, '_fields'));
        $this->assertEquals(['default' => 'asc'], $this->getProperty($model, '_order'));
    }

    public function testFindThrowsOnInvalidType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Error type for find');

        $model = new Model(
            user: 'testuser',
            appUsername: 'appuser',
            appSecret: 'secret',
            app: 'myapp',
            table: 'mytable',
        );
        $model->find('invalid');
    }

    public function testTotalDefaultsToZero(): void
    {
        $model = new Model();
        $this->assertEquals(0, $model->total);
    }

    public function testInsertReturnsApiResponse(): void
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient->method('sendRequest')
            ->willReturn(new Response(201, [], '{"_id":"abc123"}'));

        $model = new Model(
            user: 'testuser',
            appUsername: 'appuser',
            appSecret: 'secret',
            app: 'myapp',
            table: 'mytable',
            httpClient: $mockClient,
        );

        $response = $model->insert(['name' => 'test']);
        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertEquals(201, $response->statusCode);
    }

    public function testFindReturnsPaginatedResponse(): void
    {
        $responseData = json_encode([
            '_total' => 2,
            0 => ['_id' => '1', 'name' => 'Alice'],
            1 => ['_id' => '2', 'name' => 'Bob'],
        ]);

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient->method('sendRequest')
            ->willReturn(new Response(200, [], $responseData));

        $model = new Model(
            user: 'testuser',
            appUsername: 'appuser',
            appSecret: 'secret',
            app: 'myapp',
            table: 'mytable',
            httpClient: $mockClient,
        );

        $response = $model->findAll();
        $this->assertInstanceOf(PaginatedResponse::class, $response);
        $this->assertEquals(2, $response->total);
        $this->assertCount(2, $response->items);
        $this->assertEquals(2, $model->total);
    }

    public function testFindFirstReturnsSingleItem(): void
    {
        $responseData = json_encode([
            '_total' => 1,
            0 => ['_id' => '1', 'name' => 'Alice'],
        ]);

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient->method('sendRequest')
            ->willReturn(new Response(200, [], $responseData));

        $model = new Model(
            user: 'testuser',
            appUsername: 'appuser',
            appSecret: 'secret',
            app: 'myapp',
            table: 'mytable',
            httpClient: $mockClient,
        );

        $response = $model->findFirst(['name' => 'Alice']);
        $this->assertInstanceOf(PaginatedResponse::class, $response);
        $this->assertCount(1, $response->items);
    }

    private function getProperty(object $obj, string $prop): mixed
    {
        $ref = new \ReflectionProperty($obj, $prop);
        return $ref->getValue($obj);
    }
}
