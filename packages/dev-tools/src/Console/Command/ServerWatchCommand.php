<?php

declare(strict_types=1);

namespace Delirium\DevTools\Console\Command;

use Delirium\DevTools\ProcessManager;
use Delirium\DevTools\Watcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'server:watch',
    description: 'Start server with live reload (Watcher)'
)]
class ServerWatchCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('file', '', InputOption::VALUE_NONE, 'The file to run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $liveReload = true;
        // 2. Check Configuration
        if (!$liveReload) {
            $output->writeln("<comment>Live Reload is disabled.</comment>");
            $output->writeln("Enable it by setting 'liveReload: true' in your AppOptions.");
            return Command::SUCCESS;
        }

        $dirs = ['src', 'packages', 'public', 'config'];
        $output->writeln("<info>Starting Watcher...</info>");
        $output->writeln("Watching directories: " . implode(', ', $dirs));

        // 3. Run Watcher
        // We use bin/console server:start instead of bin/server
        $projectRoot = getcwd(); // Assuming run from root


        if(file_exists($projectRoot . '/bin/console')) {
            $cmd = [PHP_BINARY, $projectRoot . '/bin/console', 'server:start'];
        } else {
            $cmd = [PHP_BINARY, $projectRoot . '/vendor/bin/console', 'server:start'];
        }

        $processManager = new ProcessManager($cmd, $projectRoot);
        $watcher = new Watcher($dirs, $processManager);
        $watcher->watch();

        return Command::SUCCESS;
    }
}
