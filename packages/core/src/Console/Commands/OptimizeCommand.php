<?php

declare(strict_types=1);

namespace Delirium\Core\Console\Commands;

use Delirium\Core\Foundation\ProviderRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'optimize', description: 'Cache the framework bootstrap files')]
final class OptimizeCommand extends Command
{
    public function __construct(
        private ?ContainerInterface $container = null
    ) {
        parent::__construct();
    }

    // Setter injection to allow container to be set after instantiation if needed
    // or constructor injection if instantiated by DI
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->container || !$this->container->has(ProviderRepository::class)) {
            $io->error('ProviderRepository service not found in container.');
            return Command::FAILURE;
        }

        /** @var ProviderRepository $repository */
        $repository = $this->container->get(ProviderRepository::class);

        $io->text('Generating provider discovery cache...');

        // TODO: In the future, we might want to also cache aliases here
        // For now, we only cache providers. The aliases array would need to be
        // collected from AliasLoader if we want them persisted too.
        // But ProviderRepository already has access to aliases if they were
        // registered via Application::alias().
        //
        // Wait, bin/console bootstraps repository but Application instantiates AliasLoader.
        // If we run `optimize`, we want to cache what's configured.
        // Since bin/console doesn't load the full Application config (routes, etc),
        // this optimize command will only cache providers registered in bin/console.
        // This is a limitation of the current console setup not booting the full App.

        $repository->cache();

        $io->success('Framework bootstrap files cached successfully.');

        return Command::SUCCESS;
    }
}
