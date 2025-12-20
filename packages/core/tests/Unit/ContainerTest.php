<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Delirium\Core\Container\Container;
use Delirium\Core\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class ContainerTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $container = new Container();
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testCanSetAndGetParameter(): void
    {
        $container = new Container();
        $container->set('config.port', fn() => 8080);
        
        $this->assertTrue($container->has('config.port'));
        $this->assertEquals(8080, $container->get('config.port'));
    }

    public function testCanSetAndGetInstance(): void
    {
        $container = new Container();
        $instance = new \stdClass();
        $container->set('my_service', $instance);
        
        $this->assertSame($instance, $container->get('my_service'));
    }

    public function testGetThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $container = new Container();
        $container->get('non_existent');
    }
}
