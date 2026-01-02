<?php

declare(strict_types=1);

namespace Delirium\Core\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'server:start',
    description: 'Start the application server'
)]
class ServerCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'The file to run', 'public/index.php');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $port = (int) $input->getOption('port');
        // $host = (string) $input->getOption('host');
        // $force = (bool) $input->getOption('force');

        // // Check if port is in use
        // if ($this->isPortInUse($host, $port)) {
        //     $output->writeln("<comment>Port {$port} is already in use.</comment>");

        //     if (!$force) {
        //         $output->writeln("<error>Failed to listen server port[{$host}:{$port}]. Address already in use.</error>");
        //         $output->writeln("Use <info>--force</info> or <info>-f</info> to kill the process occupying the port.");
        //         return Command::FAILURE;
        //     }

        //     // Attempt to kill
        //     $output->writeln("Attempting to kill process occupying port {$port}...");
        //     if ($this->killProcessOnPort($port)) {
        //         $output->writeln("<info>Process killed.</info>");
        //         // Wait a bit for OS to release port
        //         sleep(1);
        //     } else {
        //         $output->writeln("<error>Failed to kill process or no suitable tool found (lsof/fuser/netstat missing?).</error>");
        //         return Command::FAILURE;
        //     }
        // }

        $fileStart = getcwd() . '/' . ltrim($input->getOption('file'), '/');

        if(!file_exists($fileStart)) {
            $output->writeln("<error>File {$fileStart} does not exist.</error>");
            return Command::FAILURE;
        }

        require $fileStart;

        return Command::SUCCESS;
    }

    // private function isPortInUse(string $host, int $port): bool
    // {
    //     $connection = @fsockopen($host, $port);
    //     if (is_resource($connection)) {
    //         fclose($connection);
    //         return true;
    //     }
    //     return false;
    // }

    // private function killProcessOnPort(int $port): bool
    // {
    //     // Try fuser
    //     exec("fuser -k -n tcp {$port} 2>&1", $output, $returnVar);
    //     if ($returnVar === 0) return true;

    //     // Try lsof + kill
    //     $pid = trim(shell_exec("lsof -t -i:{$port} -sTCP:LISTEN") ?? '');
    //     if (!empty($pid) && is_numeric($pid)) {
    //          exec("kill -9 {$pid}");
    //          exec('pkill -9 -f "bin/console"');
    //          return true;
    //     }

    //     return false;
    // }
}
