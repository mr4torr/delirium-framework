<?php

declare(strict_types=1);

namespace Delirium\Compile\Service;

use Delirium\Compile\Config\CompileConfig;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class StagingManager
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = rtrim($rootDir, '/');
    }

    public function setupStaging(CompileConfig $config, OutputInterface $output): string
    {
        $stagingDir = $this->rootDir . '/build/staging';
        
        $output->writeln("<info>Setting up staging directory at: {$stagingDir}</info>");

        // 1. Clean previous staging
        if (is_dir($stagingDir)) {
            $this->removeDirectory($stagingDir);
        }
        if (!mkdir($stagingDir, 0755, true) && !is_dir($stagingDir)) {
             throw new RuntimeException("Failed to create staging directory: $stagingDir");
        }

        // 2. Copy allowed paths
        foreach ($config->paths as $path) {
            $source = $this->rootDir . '/' . $path;
            $dest = $stagingDir . '/' . $path;
            
            if (file_exists($source)) {
                 $output->writeln("  - Copying <comment>{$path}</comment>...");
                 $this->copyRecursive($source, $dest);
            } else {
                 $output->writeln("<comment>Warning: Source path '{$path}' not found, skipping.</comment>");
            }
        }

        // 3. Copy root composer files
        copy($this->rootDir . '/composer.json', $stagingDir . '/composer.json');
        if (file_exists($this->rootDir . '/composer.lock')) {
            copy($this->rootDir . '/composer.lock', $stagingDir . '/composer.lock');
        }

        // 4. Install production dependencies
        $output->writeln("<info>Installing production dependencies (no-dev)...</info>");
        $command = 'composer install --no-dev --prefer-dist --optimize-autoloader --no-progress --working-dir=' . escapeshellarg($stagingDir);
        
        exec($command . ' 2>&1', $outputLines, $returnCode);
        
        if ($returnCode !== 0) {
             throw new RuntimeException("Composer install failed:\n" . implode("\n", $outputLines));
        }

        return $stagingDir;
    }

    public function cleanup(string $stagingDir): void
    {
        $this->removeDirectory($stagingDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $realPath = $fileinfo->getRealPath();
            if ($realPath === false) {
                // If getRealPath fails (e.g. broken link), try getPathname
                $realPath = $fileinfo->getPathname();
            }
            
            if ($fileinfo->isDir()) {
                rmdir($realPath);
            } else {
                unlink($realPath);
            }
        }
        rmdir($dir);
    }

    private function copyRecursive(string $source, string $dest): void
    {
        if (is_dir($source)) {
            if (!is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $subPath = $iterator->getSubPathName();
                $target = $dest . '/' . $subPath;
                if ($item->isDir()) {
                    if (!is_dir($target)) {
                        mkdir($target, 0755, true);
                    }
                } else {
                    copy($item->getRealPath(), $target);
                }
            }
        } else {
            // Ensure parent dir exists
            $parent = dirname($dest);
            if (!is_dir($parent)) {
                 mkdir($parent, 0755, true);
            }
            copy($source, $dest);
        }
    }
}
