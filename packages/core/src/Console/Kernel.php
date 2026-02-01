<?php

declare(strict_types=1);

namespace Delirium\Core\Console;

use Delirium\DevTools\Console\Commands\ServerWatchCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

class Kernel extends ConsoleApplication
{
    public function __construct()
    {
        parent::__construct('Delirium Framework', '1.1.0');

        // Register Core Commands
        $this->addCommands([
            new Commands\ServerCommand(),
            new Commands\SwooleCheckCommand(),
        ]);

        // Register DevTools Commands if available
        if (class_exists(ServerWatchCommand::class)) {
            $this->addCommands([
                new ServerWatchCommand(),
            ]);
        }
    }
}
