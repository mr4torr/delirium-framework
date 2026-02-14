<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Integration;

use Delirium\Core\Console\Command\CacheClearCommand;
use Delirium\Core\Console\Contract\RegenerationListenerInterface;
use Delirium\Core\Foundation\Cache\RegenerationRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Integration test for CacheClearCommand.
 */
class CacheClearCommandTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = getcwd() . '/var/cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Test that executing the command clears the directory and triggers listeners.
     */
    public function testExecuteClearsAndWarmsUp(): void
    {
        // 1. Setup - Create dummy files
        $dummyFile = $this->cacheDir . '/dummy_test.txt';
        file_put_contents($dummyFile, 'to be deleted');

        $subDir = $this->cacheDir . '/subdir_test';
        if (!is_dir($subDir)) {
            mkdir($subDir);
        }
        file_put_contents($subDir . '/nested.txt', 'to be deleted');

        $this->assertFileExists($dummyFile);
        $this->assertFileExists($subDir . '/nested.txt');

        // 2. Setup - Command and Registry
        $registry = new RegenerationRegistry();

        $listener = $this->createMock(RegenerationListenerInterface::class);
        $listener->method('shouldRun')->willReturn(true);
        $listener->method('getName')->willReturn('Mock Listener');
        $listener->expects($this->once())->method('regenerate')->willReturnCallback(function() {
            file_put_contents($this->cacheDir . '/regenerated_test.txt', 'freshly warmed');
        });

        $registry->register($listener);

        $command = new CacheClearCommand();
        $command->setRegistry($registry);

        $app = new ConsoleApplication();
        $app->addCommand($command);

        $tester = new CommandTester($app->find('cache:clear'));
        $exitCode = $tester->execute([]);

        // 3. Assertions
        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Cache cleared successfully', $tester->getDisplay());
        $this->assertStringContainsString('Cache warmed up successfully', $tester->getDisplay());

        // Verify files are deleted
        $this->assertFileDoesNotExist($dummyFile);
        $this->assertFileDoesNotExist($subDir . '/nested.txt');
        $this->assertDirectoryDoesNotExist($subDir);

        // Verify root directory preserved
        $this->assertDirectoryExists($this->cacheDir);

        // Verify warmup worked
        $this->assertFileExists($this->cacheDir . '/regenerated_test.txt');
        $this->assertEquals('freshly warmed', file_get_contents($this->cacheDir . '/regenerated_test.txt'));

        // Final cleanup of the test file
        if (file_exists($this->cacheDir . '/regenerated_test.txt')) {
            unlink($this->cacheDir . '/regenerated_test.txt');
        }
    }

    /**
     * Test that the command returns exit code 0 even if the cache directory is already empty.
     */
    public function testExecuteOnEmptyDirectoryReturnsSuccess(): void
    {
        // 1. Setup - Ensure empty directory
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }

        // 2. Setup - Command (no listeners for this test)
        $command = new CacheClearCommand();
        $app = new ConsoleApplication();
        $app->addCommand($command);

        $tester = new CommandTester($app->find('cache:clear'));
        $exitCode = $tester->execute([]);

        // 3. Assertions
        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Cache cleared successfully', $tester->getDisplay());
    }

    /**
     * Test that the command returns FAILURE (non-zero) when the directory is not writable.
     */
    public function testExecuteOnNonWritableDirectoryReturnsFailure(): void
    {
        // 1. Setup - Make directory read-only
        chmod($this->cacheDir, 0555);

        try {
            // 2. Setup - Command
            $command = new CacheClearCommand();
            $app = new ConsoleApplication();
            $app->addCommand($command);

            $tester = new CommandTester($app->find('cache:clear'));
            $exitCode = $tester->execute([]);

            // 3. Assertions
            $this->assertEquals(1, $exitCode);
            $this->assertStringContainsString('notwritable', str_replace(["\n", " "], '', $tester->getDisplay()));
        } finally {
            // Cleanup: restore permissions
            chmod($this->cacheDir, 0777);
        }
    }
}
