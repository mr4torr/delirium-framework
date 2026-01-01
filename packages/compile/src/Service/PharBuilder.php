<?php

declare(strict_types=1);

namespace Delirium\Compile\Service;

use Delirium\Compile\Config\CompileConfig;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class PharBuilder
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = rtrim($rootDir, '/');
    }

    public function build(string $stagingDir, CompileConfig $config, OutputInterface $output): string
    {
        $pharFile = $this->rootDir . '/build/' . $config->outputName . '.phar';

        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $output->writeln("<info>Creating PHAR at: {$pharFile}</info>");

        $phar = new Phar($pharFile);
        
        // Start buffering changes
        $phar->startBuffering();

        // Recursively add files from staging directory
        $output->writeln("  - Adding files from staging...");
        $this->addFiles($phar, $stagingDir, $output);

        // Set stub
        // We use bin/server as the entry point.
        // We need to wrap it to map the phar.
        $stub = $this->generateStub('bin/server');
        $phar->setStub($stub);

        // Stop buffering and save
        $phar->stopBuffering();
        
        // Make executable
        chmod($pharFile, 0755);
        $output->writeln("<info>PHAR built successfully.</info>");

        return $pharFile;
    }

    private function addFiles(Phar $phar, string $stagingDir, OutputInterface $output): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stagingDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            // Get relative path from stagingDir
            // $stagingDir = /path/to/build/staging
            // $item = /path/to/build/staging/vendor/autoload.php
            // relative = vendor/autoload.php
            
            // Note: getSubPathName() returns path relative to the iterator root
            $relativePath = $iterator->getSubPathName();

            if ($item->isDir()) {
                $phar->addEmptyDir($relativePath);
            } else {
                $phar->addFile($item->getRealPath(), $relativePath);
            }
        }
    }

    private function generateStub(string $entryScript): string
    {
        return <<<STUB
#!/usr/bin/env php
<?php
Phar::mapPhar();
require 'phar://' . __FILE__ . '/{$entryScript}';
__HALT_COMPILER();
STUB;
    }
}
