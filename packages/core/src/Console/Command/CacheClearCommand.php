<?php

declare(strict_types=1);

namespace Delirium\Core\Console\Command;

use Delirium\Core\Foundation\Cache\RegenerationRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

/**
 * Class CacheClearCommand
 *
 * Command to clear the application cache (var/cache) and trigger
 * regeneration of essential bootstrap files via registered listeners.
 */
#[AsCommand(name: 'cache:clear', description: 'Clears the application cache and triggers regeneration.')]
class CacheClearCommand extends Command
{
    private ?ContainerInterface $container = null;
    private ?RegenerationRegistry $registry = null;

    /**
     * Set the container for listener discovery.
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Set the registry directly (useful for tests or manual setup).
     */
    public function setRegistry(RegenerationRegistry $registry): void
    {
        $this->registry = $registry;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // Default cache directory relative to project root
        $cacheDir = getcwd() . '/var/cache';

        $io->title('Delirium Cache Clear');

        // 1. Clear Phase
        if (!is_dir($cacheDir)) {
            $io->note(sprintf('Cache directory "%s" does not exist. Creating it...', $cacheDir));
            if (!mkdir($cacheDir, 0777, true)) {
                $io->error(sprintf('Failed to create cache directory "%s".', $cacheDir));
                return Command::FAILURE;
            }
        }

        if (!is_writable($cacheDir)) {
            $io->error(sprintf('Cache directory "%s" is not writable.', $cacheDir));
            return Command::FAILURE;
        }

        $io->text('Clearing cache files...');

        try {
            $this->emptyDirectory($cacheDir);
            $io->success('Cache cleared successfully.');
        } catch (\Throwable $e) {
            $io->error(sprintf('Error clearing cache: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        // 2. Warmup Phase
        $io->section('Warming up cache');

        $registry = $this->registry;

        // Try to get registry from container if not set
        if (!$registry && $this->container?->has(RegenerationRegistry::class)) {
            $registry = $this->container->get(RegenerationRegistry::class);
        }

        if (!$registry) {
            $io->warning('RegenerationRegistry not found. Skipping warmup.');
            return Command::SUCCESS;
        }

        $listeners = $registry->getListeners();
        $successCount = 0;
        $failCount = 0;

        if (empty($listeners)) {
            $io->note('No regeneration listeners registered.');
            return Command::SUCCESS;
        }

        foreach ($listeners as $listener) {
            if (!$listener->shouldRun()) {
                $io->text(sprintf('  [-] Skipping <info>%s</info> (shouldRun returned false)', $listener->getName()));
                continue;
            }

            try {
                $io->text(sprintf('  [+] Regenerating <info>%s</info>...', $listener->getName()));
                $listener->regenerate();
                $successCount++;
            } catch (\Throwable $e) {
                $io->error(sprintf('FAILED to regenerate %s: %s', $listener->getName(), $e->getMessage()));
                $failCount++;
            }
        }

        if ($failCount > 0) {
            $io->warning(sprintf('Warmup finished with %d successes and %d failures.', $successCount, $failCount));
            return Command::FAILURE;
        }

        $io->success(sprintf('Cache warmed up successfully (%d listeners executed).', $successCount));

        return Command::SUCCESS;
    }

    /**
     * Delete everything inside the directory but keep the directory itself.
     */
    private function emptyDirectory(string $dir): void
    {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $path = $item->getPathname();
            if ($item->isDir() && !$item->isLink()) {
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
}
