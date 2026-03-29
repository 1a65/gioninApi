<?php

namespace Gionin\Tests;

use Gionin\Utf8String;
use PHPUnit\Framework\TestCase;

class Utf8StringTest extends TestCase
{
    public function testIsUTF8WithValidUtf8(): void
    {
        $this->assertTrue(Utf8String::isUTF8('Hello World'));
        $this->assertTrue(Utf8String::isUTF8('São Paulo'));
        $this->assertTrue(Utf8String::isUTF8('日本語'));
    }

    public function testIsUTF8WithEmptyString(): void
    {
        $this->assertTrue(Utf8String::isUTF8(''));
    }

    public function testNoAccentsRemovesAccents(): void
    {
        $this->assertEquals('Sao Paulo', Utf8String::noAccents('São Paulo'));
        $this->assertEquals('cafe', Utf8String::noAccents('café'));
        $this->assertEquals('nao', Utf8String::noAccents('não'));
    }

    public function testLowerAndNoAccents(): void
    {
        $this->assertEquals('sao paulo', Utf8String::lowerAndNoAccents('São Paulo'));
        $this->assertEquals('cafe', Utf8String::lowerAndNoAccents('Café'));
        $this->assertEquals('hello', Utf8String::lowerAndNoAccents('HELLO'));
    }

    public function testLowerAndNoAccentsEmptyString(): void
    {
        $this->assertEquals('', Utf8String::lowerAndNoAccents(''));
    }

    public function testRebaseEncodeWithUtf8(): void
    {
        $text = 'São Paulo';
        $result = Utf8String::rebaseEncode($text);
        $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
    }
}
