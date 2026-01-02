<?php

declare(strict_types=1);

namespace Delirium\Core\Console;

use Delirium\Core\Contract\ApplicationInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

class Kernel extends ConsoleApplication
{
    public function __construct()
    {
        parent::__construct('Delirium Framework', '1.1.0');

        // Register Core Commands
        $this->addCommands([
            new Commands\ServerCommand(),
            new Commands\SwooleCheckCommand()
        ]);

        // Register DevTools Commands if available
        if (class_exists(\Delirium\DevTools\Console\Commands\ServerWatchCommand::class)) {
            $this->addCommands([
                new \Delirium\DevTools\Console\Commands\ServerWatchCommand()
            ]);
        }
    }
}
