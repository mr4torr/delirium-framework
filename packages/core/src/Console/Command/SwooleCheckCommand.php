<?php

declare(strict_types=1);

namespace Delirium\Core\Console\Command;

use Swoole\Http\Server;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'swoole:check', description: 'Verify Swoole installation and compatibility')]
class SwooleCheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Verifying Swoole installation...');

        if (!extension_loaded('swoole')) {
            $output->writeln('<error>ERROR: Swoole extension is not loaded.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Swoole extension loaded. Version: ' . swoole_version() . '</info>');

        if (!class_exists(Server::class)) {
            $output->writeln("<error>ERROR: Swoole\Http\Server class not found.</error>");
            return Command::FAILURE;
        }

        try {
            // Attempt instantiation (port 0 to pick random valid port/avoid binding, or just check existence)
            $server = new Server('127.0.0.1', 0, SWOOLE_BASE);
            $output->writeln("<info>Successfully instantiated Swoole\Http\Server.</info>");
        } catch (\Throwable $e) {
            $output->writeln('<error>ERROR: Failed to instantiate Swoole Server: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('Verification passed.');
        return Command::SUCCESS;
    }
}
