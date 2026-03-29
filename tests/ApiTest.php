<?php

namespace Gionin\Tests;

use Gionin\Api;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    private Api $api;

    protected function setUp(): void
    {
        $this->api = new Api();
    }

    public function testSetUser(): void
    {
        $this->api->setUser('testuser');

        $ref = new \ReflectionProperty($this->api, '_user');
        $this->assertEquals('testuser', $ref->getValue($this->api));
    }

    public function testSetCredentials(): void
    {
        $this->api->setCredentials('user', 'secret');

        $refUser = new \ReflectionProperty($this->api, '_authUser');
        $refKey = new \ReflectionProperty($this->api, '_authKey');

        $this->assertEquals('user', $refUser->getValue($this->api));
        $this->assertEquals('secret', $refKey->getValue($this->api));
    }

    public function testSetMethodValid(): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            $this->api->setMethod($method);
            $ref = new \ReflectionProperty($this->api, '_method');
            $this->assertEquals($method, $ref->getValue($this->api));
        }
    }

    public function testSetMethodInvalid(): void
    {
        $this->api->setMethod('INVALID');
        $ref = new \ReflectionProperty($this->api, '_method');
        $this->assertEquals('GET', $ref->getValue($this->api));
    }

    public function testSetData(): void
    {
        $data = ['key' => 'value'];
        $this->api->setData($data);

        $ref = new \ReflectionProperty($this->api, '_data');
        $this->assertEquals($data, $ref->getValue($this->api));
    }

    public function testSetUrl(): void
    {
        $this->api->setUrl('schema');
        $ref = new \ReflectionProperty($this->api, '_url');
        $this->assertStringContainsString('/v1/{user}/{app}', $ref->getValue($this->api));
    }

    public function testSetTableUrlThrowsWithoutUser(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('user not declared');

        $this->api->setApp('myapp');
        $this->api->setTable('mytable');

        $ref = new \ReflectionMethod($this->api, 'setTableUrl');
        $ref->invoke($this->api);
    }

    public function testSetTableUrlThrowsWithoutApp(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('App not declared');

        $this->api->setUser('testuser');
        $this->api->setTable('mytable');

        $ref = new \ReflectionMethod($this->api, 'setTableUrl');
        $ref->invoke($this->api);
    }

    public function testSetTableUrlThrowsWithoutTable(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Table not declared');

        $this->api->setUser('testuser');
        $this->api->setApp('myapp');

        $ref = new \ReflectionMethod($this->api, 'setTableUrl');
        $ref->invoke($this->api);
    }

    public function testSetTableUrlBuildsCorrectUrl(): void
    {
        $this->api->setUser('testuser');
        $this->api->setApp('myapp');
        $this->api->setTable('mytable');

        $ref = new \ReflectionMethod($this->api, 'setTableUrl');
        $ref->invoke($this->api);

        $urlRef = new \ReflectionProperty($this->api, '_url');
        $this->assertEquals(
            'https://api.gionin.com/v1/testuser/myapp/mytable',
            $urlRef->getValue($this->api)
        );
    }

    public function testSetDebug(): void
    {
        $this->api->setDebug(true);
        $ref = new \ReflectionProperty($this->api, '_debug');
        $this->assertTrue($ref->getValue($this->api));

        $this->api->setDebug(false);
        $this->assertFalse($ref->getValue($this->api));
    }
}
