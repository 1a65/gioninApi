<?php

namespace Gionin\Tests;

use Gionin\Model;
use PHPUnit\Framework\TestCase;

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
            debug: true
        );

        $this->assertEquals('testuser', $this->getProperty($model, '_user'));
        $this->assertEquals('appuser', $this->getProperty($model, '_authUser'));
        $this->assertEquals('secret', $this->getProperty($model, '_authKey'));
        $this->assertEquals('myapp', $this->getProperty($model, 'app'));
        $this->assertEquals('mytable', $this->getProperty($model, 'table'));
        $this->assertTrue($this->getProperty($model, '_debug'));
    }

    public function testConstructorDefaults(): void
    {
        $model = new Model();

        $this->assertEquals('', $this->getProperty($model, '_user'));
        $this->assertEquals('', $this->getProperty($model, 'app'));
        $this->assertEquals('', $this->getProperty($model, 'table'));
        $this->assertFalse($this->getProperty($model, '_debug'));
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
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error type for find');

        $model = new Model(
            user: 'testuser',
            appUsername: 'appuser',
            appSecret: 'secret',
            app: 'myapp',
            table: 'mytable'
        );
        $model->find('invalid');
    }

    public function testTotalDefaultsToZero(): void
    {
        $model = new Model();
        $this->assertEquals(0, $model->total);
    }

    private function getProperty(object $obj, string $prop): mixed
    {
        $ref = new \ReflectionProperty($obj, $prop);
        return $ref->getValue($obj);
    }
}
