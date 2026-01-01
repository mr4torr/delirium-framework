<?php

declare(strict_types=1);

namespace Delirium\Compile\Command;

use Delirium\Compile\Config\CompileConfig;
use Delirium\Compile\Service\PharBuilder;
use Delirium\Compile\Service\SpcBuilder;
use Delirium\Compile\Service\StagingManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompileCommand extends Command
{
    protected static $defaultName = 'compile';
    protected static $defaultDescription = 'Compile the application into a static binary or PHAR.';

    protected function configure(): void
    {
        $this->setName('compile')
             ->setDescription('Compile the application into a static binary or PHAR.')
             ->setHelp('This command builds the application into a standalone executable using Docker.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (ini_get('phar.readonly')) {
            $output->writeln('<error>Error: phar.readonly is enabled. Please run with -d phar.readonly=0</error>');
            return Command::FAILURE;
        }

        $rootDir = getcwd();
        $output->writeln('Starting compilation process...');
        $output->writeln('<info>Using Docker for static binary compilation.</info>');

        $config = new CompileConfig(outputName: 'delirium');
        $output->writeln("Target binary: {$config->outputName}");
        
        try {
            // 1. Staging
            $stagingManager = new StagingManager($rootDir);
            $stagingDir = $stagingManager->setupStaging($config, $output);

            // 2. PHAR
            $pharBuilder = new PharBuilder($rootDir);
            $pharFile = $pharBuilder->build($stagingDir, $config, $output);

            $output->writeln("<info>Successfully built PHAR: {$pharFile}</info>");

            // 3. Static Binary
            $spcBuilder = new SpcBuilder($rootDir);
            $spcBuilder->downloadSources($config, $output);
            $binaryFile = $spcBuilder->buildMicro($pharFile, $config, $output);

            $output->writeln("<info>Successfully built Static Binary: {$binaryFile}</info>");

        } catch (\Throwable $e) {
            $output->writeln("<error>Build failed: {$e->getMessage()}</error>");
            return Command::FAILURE;
        } finally {
            // Cleanup staging
            if (isset($stagingManager) && isset($stagingDir) && is_dir($stagingDir)) {
                $output->writeln('Cleaning up staging directory...');
                $stagingManager->cleanup($stagingDir);
            }
        }

        $output->writeln('Done.');

        return Command::SUCCESS;
    }
}
