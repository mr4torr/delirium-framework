<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Message;

use Delirium\Http\Message\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class ResponseTest extends TestCase
{
    private $psrResponse;
    private $streamFactory;
    private $response;

    protected function setUp(): void
    {
        $this->psrResponse = $this->createMock(ResponseInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->response = new Response($this->psrResponse, $this->streamFactory);
    }

    public function testJson()
    {
        $data = ['foo' => 'bar'];
        $stream = $this->createMock(StreamInterface::class);

        $this->streamFactory->method('createStream')
            ->with(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->willReturn($stream);

        $this->psrResponse->expects($this->once())->method('withHeader')->with('Content-Type', 'application/json')->willReturnSelf();
        $this->psrResponse->expects($this->once())->method('withBody')->with($stream)->willReturnSelf();

        $this->response->json($data);
    }

    public function testRedirect()
    {
        $url = '/target';

        $this->psrResponse->expects($this->once())->method('withStatus')->with(302, '')->willReturnSelf();
        $this->psrResponse->expects($this->once())->method('withHeader')->with('Location', $url)->willReturnSelf();

        $this->response->redirect($url);
    }

    public function testWithCookie()
    {
        $this->psrResponse->expects($this->once())
            ->method('withAddedHeader')
            ->with('Set-Cookie', 'key=value')
            ->willReturnSelf();

        $this->response->withCookie('key', 'value');
    }

    public function testDownload()
    {
        $file = tempnam(sys_get_temp_dir(), 'test_dl');
        file_put_contents($file, 'content');

        $stream = $this->createMock(StreamInterface::class);
        $this->streamFactory->method('createStreamFromFile')->with($file)->willReturn($stream);

        $this->psrResponse->method('withHeader')->willReturnSelf();
        $this->psrResponse->method('withBody')->with($stream)->willReturnSelf();

        $this->response->download($file, 'custom.txt');

        unlink($file);
    }

    public function testXml()
    {
        $data = ['item' => 'value'];
        $stream = $this->createMock(StreamInterface::class);

        // XML output for simple array
        $expectedXml = "<?xml version=\"1.0\"?>\n<root><item>value</item></root>\n";

        $this->streamFactory->method('createStream')->with($expectedXml)->willReturn($stream);
        $this->psrResponse->method('withHeader')->with('Content-Type', 'application/xml')->willReturnSelf();
        $this->psrResponse->method('withBody')->with($stream)->willReturnSelf();

        $this->response->xml($data);
    }
}
