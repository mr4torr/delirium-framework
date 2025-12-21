<?php

declare(strict_types=1);

namespace Delirium\DI;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Delirium\DI\Contract\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class ContainerBuilder
{
    private SymfonyContainerBuilder $container;

    public function __construct()
    {
        $this->container = new SymfonyContainerBuilder();
        $this->container->setAlias(ContainerInterface::class, 'service_container')->setPublic(true);
        $this->container->addCompilerPass(new Compiler\DiscoveryPass());
        $this->container->addCompilerPass(new Compiler\PropertyInjectionPass());
    }

    public function addCompilerPass(CompilerPassInterface $pass, string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0): self
    {
        $this->container->addCompilerPass($pass, $type, $priority);
        return $this;
    }

    public function register(string $id, string $class): \Symfony\Component\DependencyInjection\Definition
    {
        return $this->container->register($id, $class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(true); // Default to public for MVP
    }

    public function getInnerBuilder(): SymfonyContainerBuilder
    {
        return $this->container;
    }

    public function build(string $environment): ContainerInterface
    {
        // Register default configuration or parameters if needed
        $this->container->setParameter('kernel.environment', $environment);
        
        // Optimize and compile the container
        $this->container->compile();

        return $this->container;
    }

    public function dump(string $path): void
    {
        $dumper = new PhpDumper($this->container);
        $content = $dumper->dump([
            'class' => 'ProjectServiceContainer',
            'namespace' => 'Delirium\DI\Cache',
        ]);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $content);
    }
}
