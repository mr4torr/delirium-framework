<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Unit\Console\Command;

use Delirium\Http\Console\Command\RouteListCommand;
use Delirium\Http\RouteRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RouteListCommandTest extends TestCase
{
    private RouteListCommand $command;
    private RouteRegistry&MockObject $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RouteRegistry::class);
        $this->command = new RouteListCommand($this->registry);
    }

    public function testItDisplaysRegisteredRoutes(): void
    {
        $this->registry->method('getRoutes')->willReturn([
            'GET' => [
                '/' => ['App\Controller\HomeController', 'index'],
                '/about' => function () {},
            ],
            'POST' => [
                '/api/users' => 'App\Controller\UserController::store',
            ],
        ]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        $output = $tester->getDisplay();

        $this->assertStringContainsString('GET', $output);
        $this->assertStringContainsString('/', $output);
        $this->assertStringContainsString('App\Controller\HomeController::index', $output);

        $this->assertStringContainsString('/about', $output);
        $this->assertStringContainsString('Closure', $output);

        $this->assertStringContainsString('POST', $output);
        $this->assertStringContainsString('/api/users', $output);
        $this->assertStringContainsString('App\Controller\UserController::store', $output);
    }

    public function testItDisplaysEmptyState(): void
    {
        $this->registry->method('getRoutes')->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('No routes registered.', $output);
    }
}
