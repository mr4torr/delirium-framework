<?php

declare(strict_types=1);

namespace Delirium\Http\Console\Command;

use Delirium\Http\RouteRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'route:list', description: 'List all registered routes')]
class RouteListCommand extends Command
{
    public function __construct(
        private readonly RouteRegistry $registry
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Call cache:clear first if available
        if ($this->getApplication()) {
            try {
                $command = $this->getApplication()->find('cache:clear');
                $command->run($input, $output);
            } catch (\Throwable $e) {
                // Ignore if not found or execution failed
            }
        }

        $routes = $this->registry->getRoutes();
        $table = new Table($output);
        $table->setHeaders(['Method', 'URI', 'Handler']);

        // Check for empty routes
        $hasRoutes = false;
        foreach ($routes as $methodRoutes) {
            if (!empty($methodRoutes)) {
                $hasRoutes = true;
                break;
            }
        }

        if (!$hasRoutes) {
            $output->writeln('<info>No routes registered.</info>');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($routes as $method => $paths) {
            foreach ($paths as $path => $handler) {
                $handlerDisplay = $this->formatHandler($handler);
                $rows[] = [$method, $path, $handlerDisplay];
            }
        }

        // Sort by URI then Method
        usort($rows, function ($a, $b) {
            return strcmp($a[1], $b[1]);
        });

        $table->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }

    private function formatHandler(mixed $handler): string
    {
        if (is_string($handler)) {
            return $handler;
        }
        if (is_array($handler)) {
            if (isset($handler[0]) && is_string($handler[0])) {
                return $handler[0] . '::' . ($handler[1] ?? '__invoke');
            }
            if (isset($handler[0]) && is_object($handler[0])) {
                return get_class($handler[0]) . '::' . ($handler[1] ?? '__invoke');
            }
        }
        if ($handler instanceof \Closure) {
            return 'Closure';
        }
        if (is_object($handler) && method_exists($handler, '__invoke')) {
            return get_class($handler);
        }

        return 'Unknown Handler';
    }
}
