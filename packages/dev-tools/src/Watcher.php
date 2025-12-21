<?php

declare(strict_types=1);

namespace Delirium\DevTools;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Watcher
{
    /** @var array<string, int> */
    private array $checksums = [];

    /**
     * @param string[] $dirs Directories to watch
     * @param ProcessManager $processManager
     * @param int $sleepSeconds Polling interval
     */
    public function __construct(
        private readonly array $dirs,
        private readonly ProcessManager $processManager,
        private readonly int $sleepSeconds = 1
    ) {
    }

    public function watch(): void
    {
        $this->processManager->start();
        $this->checksums = $this->calculateChecksums();

        echo "[Watcher] Started. Watching: " . implode(', ', $this->dirs) . "\n";
        echo "[Watcher] Press CTRL+C to stop.\n";

        // Ensure child process is stopped on exit
        register_shutdown_function(function () {
            $this->processManager->stop();
        });

        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGINT, function () {
                echo "\n[Watcher] Stopping...\n";
                exit(0); // Will trigger shutdown function
            });
        }

        while (true) {
            sleep($this->sleepSeconds);
            
            $newChecksums = $this->calculateChecksums();
            if ($newChecksums !== $this->checksums) {
                echo "[Watcher] Change detected. Restarting...\n";
                $this->processManager->restart();
                $this->checksums = $newChecksums;
            }
        }
    }

    /**
     * @return array<string, int>
     */
    private function calculateChecksums(): array
    {
        $checksums = [];

        foreach ($this->dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir)
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $path = $file->getPathname();
                    // Basic filtering
                    if (str_contains($path, '/vendor/') || str_contains($path, '/var/') || str_contains($path, '/.git/')) {
                        continue;
                    }

                    $checksums[$path] = $file->getMTime();
                }
            }
        }

        return $checksums;
    }
}
