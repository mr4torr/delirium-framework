<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Message;

use Delirium\Http\Message\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class RequestTest extends TestCase
{
    public function testInputPrioritizesBody()
    {
        $mock = $this->createMock(ServerRequestInterface::class);
        $mock->method('getParsedBody')->willReturn(['foo' => 'bar_body']);
        $mock->method('getQueryParams')->willReturn(['foo' => 'bar_query']);

        $request = new Request($mock);
        $this->assertEquals('bar_body', $request->input('foo'));
    }

    public function testInputFallbacksToQuery()
    {
        $mock = $this->createMock(ServerRequestInterface::class);
        $mock->method('getParsedBody')->willReturn([]);
        $mock->method('getQueryParams')->willReturn(['foo' => 'bar_query']);

        $request = new Request($mock);
        $this->assertEquals('bar_query', $request->input('foo'));
    }

    public function testHeaderReturnsDefault()
    {
        $mock = $this->createMock(ServerRequestInterface::class);
        $mock->method('hasHeader')->willReturnMap([
            ['X-Missing', false],
            ['X-Existing', true],
        ]);
        $mock->method('getHeaderLine')->with('X-Existing')->willReturn('exists');

        $request = new Request($mock);
        $this->assertEquals('default', $request->header('X-Missing', 'default'));
        $this->assertEquals('exists', $request->header('X-Existing'));
    }

    public function testFileReturnsUploadedFile()
    {
        $fileMock = $this->createMock(UploadedFileInterface::class);
        $mock = $this->createMock(ServerRequestInterface::class);
        $mock->method('getUploadedFiles')->willReturn(['avatar' => $fileMock]);

        $request = new Request($mock);
        $this->assertSame($fileMock, $request->file('avatar'));
        $this->assertNull($request->file('missing'));
    }

    public function testAllMergesParams()
    {
        $mock = $this->createMock(ServerRequestInterface::class);
        $mock->method('getParsedBody')->willReturn(['a' => 1]);
        $mock->method('getQueryParams')->willReturn(['b' => 2]);

        $request = new Request($mock);
        $this->assertEquals(['a' => 1, 'b' => 2], $request->all());
        $this->assertEquals(['a' => 1], $request->all(['a']));
    }
}
