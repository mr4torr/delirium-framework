<?php

declare(strict_types=1);

namespace Delirium\Core;

use Delirium\Core\Contract\ApplicationInterface;
use Delirium\DI\ContainerBuilder;
use Delirium\Http\Contract\RouterInterface;
use Delirium\Http\DependencyInjection\Compiler\RoutePass;
use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Static Factory to bootstrap the application.
 */
class AppFactory
{
    /**
     * Create an Application instance.
     *
     * @param string $moduleClass The root module class name.
     * @param AppOptions|null $options Configuration options.
     * @return ApplicationInterface
     */
    public static function create(string $moduleClass, ?AppOptions $options = null): ApplicationInterface
    {
        // 1. Initialize Options
        $appOptions = $options ?? new AppOptions();

        // 2. Initialize Container (Cached or New)
        $cacheFile = getcwd() . '/var/cache/dependency-injection.php';

        $debugOptions = $appOptions->get(Options\DebugOptions::class);
        $debug = $debugOptions ? $debugOptions->debug : false;

        if (!$debug && file_exists($cacheFile)) {
            require_once $cacheFile;
            // The dumped container class is usually 'Delirium\DI\Cache\ProjectServiceContainer'
            /** @var ContainerInterface $container */
            $container = new \Delirium\DI\Cache\ProjectServiceContainer();
        } else {
            $builder = new ContainerBuilder();
            $container = $builder->getInnerBuilder();
            $container->addCompilerPass(new RoutePass());

            $builder->register(RouteRegistry::class, RouteRegistry::class);
            $builder->register(Router::class, Router::class);

            $container->setAlias('router', Router::class)->setPublic(true);
            $container->setAlias(RouterInterface::class, Router::class)->setPublic(true);

            // PSR-17 Factories
            $builder->register(ResponseFactoryInterface::class, \Nyholm\Psr7\Factory\Psr17Factory::class);
            $builder->register(ServerRequestFactoryInterface::class, \Nyholm\Psr7\Factory\Psr17Factory::class);
            $builder->register(StreamFactoryInterface::class, \Nyholm\Psr7\Factory\Psr17Factory::class);
            $builder->register(UploadedFileFactoryInterface::class, \Nyholm\Psr7\Factory\Psr17Factory::class);
            $builder->register(UriFactoryInterface::class, \Nyholm\Psr7\Factory\Psr17Factory::class);

            // Bind Delirium Contracts to Implementations (where possible)
            // Note: Request is request-scoped, so usually handled by Resolvers, but we register for completeness/static analysis
            $builder->register(\Delirium\Http\Contract\ResponseInterface::class, \Delirium\Http\Message\Response::class);
            // $builder->register(\Delirium\Http\Contract\RequestInterface::class, \Delirium\Http\Message\Request::class);

            // Aliases for functional requirement
            // Note: These will point to the *services* registered, not magically the active request instance unless that service is proxying or scoped correctly.
            // But requirement says "->get('request')".
            // Since we don't have request scope in container yet (Swoole context handles it), we map interface names.
            // For now, mapping aliases to Interfaces.
            $container->setAlias('response', \Delirium\Http\Contract\ResponseInterface::class);
            $container->setAlias('request', ServerRequestFactoryInterface::class); // best guess for now or just generic RequestInterface?
            // Actually 'request' usually means the Current Request. Without RequestScope in DI, this is tricky.
            // But per requirement "A chamada ->get('request') retorne uma instância compatível com RequestInterface".
            // We'll trust the ContainerServiceResolver handles injection in controllers.
            // But for explicit $container->get(), we need a service.
            // Let's bind 'response' to ResponseInterface (which is bound to Response class).
            // 'request' is harder. Assuming it means "Factory" or "Empty Request" if outside context.
            $builder->register('request', \Delirium\Http\Message\Request::class); // Register class
            $builder->register('response', \Delirium\Http\Message\Response::class);

            // Scan Module
            $visited = [];
            self::scanModule($moduleClass, $builder, $visited);

            // Build and Dump
            $builder->build($debug ? 'dev' : 'prod'); // env

            if(!$debug) {
                $builder->dump($cacheFile);
            }

            /** @var ContainerInterface */
            $container = $builder->getInnerBuilder();
        }

        $router = $container->get(Router::class);
        $router->setContainer($container);

        // Configure Dispatcher with Argument Resolvers
        $dispatcher = new \Delirium\Http\Dispatcher\RegexDispatcher();
        $dispatcher->setContainer($container);

        // We use Nyholm/Psr7 factory implementation strictly
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

        // Resolver Chain
        $chainRequest = new \Delirium\Http\Resolver\ArgumentResolverChain([
            new \Delirium\Http\Resolver\Request\ServerRequestResolver(),
            new \Delirium\Http\Resolver\Request\RouteParameterResolver(),
            new \Delirium\Core\Resolver\PayloadResolver(
                new \Delirium\Core\Hydrator\ObjectHydrator(),
                new \Delirium\Validation\Adapter\SymfonyValidatorAdapter()
            ), // Feature 006
            new \Delirium\Http\Resolver\Request\ContainerServiceResolver($container),
            new \Delirium\Http\Resolver\Request\ResponseResolver($psr17Factory, $psr17Factory),
            new \Delirium\Http\Resolver\Request\DefaultValueResolver(),
        ]);

        $chainResponse = new \Delirium\Http\Resolver\Response\ResponseResolverChain([
            new \Delirium\Http\Resolver\Response\JsonResolver($psr17Factory, $psr17Factory),
            new \Delirium\Http\Resolver\Response\XmlResolver($psr17Factory, $psr17Factory),
            new \Delirium\Http\Resolver\Response\StreamResolver($psr17Factory, $psr17Factory),
            new \Delirium\Http\Resolver\Response\HtmlResolver($psr17Factory, $psr17Factory),
            new \Delirium\Http\Resolver\Response\DefaultValueResolver($psr17Factory, $psr17Factory),
        ]);

        $dispatcher->setArgumentResolverChain($chainRequest);
        $dispatcher->setResponseResolverChain($chainResponse);
        $router->setDispatcher($dispatcher);

        // 5. Create Adapter
        // We use Nyholm/Psr7 factory implementation strictly
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $adapter = new \Delirium\Http\Bridge\SwoolePsrAdapter(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // StreamFactory
            $psr17Factory  // UploadedFileFactory
        );

        // 6. Create Application
        return new Application($container, $appOptions, $router, $adapter);
    }

    /**
     * Recursively scan modules.
     *
     * @param string $moduleClass
     * @param ContainerBuilder $builder
     * @param array<string, bool> $visited
     */
    private static function scanModule(string $moduleClass, ContainerBuilder $builder, array &$visited): void
    {
        if (isset($visited[$moduleClass])) {
            return; // Cycle detected or already visited
        }
        $visited[$moduleClass] = true;

        if (!class_exists($moduleClass)) {
            throw new \InvalidArgumentException("Module class '$moduleClass' not found.");
        }

        $ref = new \ReflectionClass($moduleClass);
        $attributes = $ref->getAttributes(\Delirium\Core\Attribute\Module::class);

        if (empty($attributes)) {
            throw new \InvalidArgumentException("Class '$moduleClass' is not annotated with #[AppModule].");
        }

        /** @var \Delirium\Core\Attribute\Module $module */
        $module = $attributes[0]->newInstance();

        // Register Providers
        foreach ($module->providers as $provider) {
            if (is_string($provider)) {
                $builder->register($provider, $provider);
            } elseif (is_callable($provider)) {
                // TODO: Support callable/factory providers with ID
            }
        }

        // Register Controllers
        foreach ($module->controllers as $controller) {
            // Registration in DI
            $builder->register($controller, $controller);
        }

        // Recurse Imports
        foreach ($module->imports as $import) {
            self::scanModule($import, $builder, $visited);
        }
    }
}
