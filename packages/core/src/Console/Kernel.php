<?php

declare(strict_types=1);

namespace Delirium\Core\Console;

use Symfony\Component\Console\Application as ConsoleApplication;

class Kernel extends ConsoleApplication
{
    private static ?self $instance = null;

    public function __construct()
    {
        parent::__construct('Delirium Framework', '1.1.0');

        self::$instance = $this;

        // Register Core Commands
        $this->addCommands([
            new Commands\ServerCommand(),
            new Commands\SwooleCheckCommand(),
            new Commands\OptimizeCommand(),
        ]);
    }

    /**
     * Get the current Kernel instance (for provider access).
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }
}
