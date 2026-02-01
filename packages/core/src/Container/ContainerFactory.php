<?php

declare(strict_types=1);

namespace Delirium\Core\Container;

use Delirium\Core\AppOptions;
use Delirium\Core\Module\ModuleScanner;
use Delirium\Core\Options\DebugOptions;
use Delirium\DI\ContainerBuilder;
use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Contract\RouterInterface;
use Delirium\Http\DependencyInjection\Compiler\RoutePass;
use Delirium\Http\Message\Request;
use Delirium\Http\Message\Response;
use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ContainerFactory
{
    public function create(string $moduleClass, AppOptions $options): ContainerInterface
    {
        $debugOptions = $options->get(DebugOptions::class);
        $debug = $debugOptions ? $debugOptions->debug : false;

        $cacheFile = getcwd() . '/var/cache/dependency-injection.php';

        if (!$debug && file_exists($cacheFile)) {
            require_once $cacheFile;
            $containerClass = '\Delirium\DI\Cache\ProjectServiceContainer';
            if (class_exists($containerClass)) {
                return new $containerClass();
            }
        }

        return $this->buildContainer($moduleClass, $options, $cacheFile);
    }

    private function buildContainer(string $moduleClass, AppOptions $options, string $cacheFile): ContainerInterface
    {
        $debugOptions = $options->get(DebugOptions::class);
        $debug = $debugOptions ? $debugOptions->debug : false;
        $builder = new ContainerBuilder();
        $container = $builder->getInnerBuilder(); // Access Symfony builder for simpler alias/compiler pass setup if needed,
        // but we should use Delirium Builder methods where possible.

        // Register Compiler Passes
        $container->addCompilerPass(new RoutePass());

        // Register Core Services
        $builder->register(RouteRegistry::class, RouteRegistry::class);
        $builder->register(Router::class, Router::class);

        // Aliases
        $container->setAlias('router', Router::class)->setPublic(true);
        $container->setAlias(RouterInterface::class, Router::class)->setPublic(true);

        // PSR-17 Factories
        $builder->register(ResponseFactoryInterface::class, Psr17Factory::class);
        $builder->register(ServerRequestFactoryInterface::class, Psr17Factory::class);
        $builder->register(StreamFactoryInterface::class, Psr17Factory::class);
        $builder->register(UploadedFileFactoryInterface::class, Psr17Factory::class);
        $builder->register(UriFactoryInterface::class, Psr17Factory::class);

        // Bind Delirium Contracts
        $builder->register(ResponseInterface::class, Response::class);

        // Legacy/User-facing Aliases
        $container->setAlias('response', ResponseInterface::class);
        $builder->register('request', Request::class);
        $builder->register('response', Response::class);

        // Scan Module
        $moduleScanner = new ModuleScanner();
        $moduleScanner->scan($moduleClass, $builder);

        // Build
        $builder->build($debug ? 'dev' : 'prod');

        // Dump if not debug
        if (!$debug) {
            $builder->dump($cacheFile);
        }

        return $builder->getInnerBuilder();
    }
}
