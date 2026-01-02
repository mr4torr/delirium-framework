<?php

declare(strict_types=1);

namespace Delirium\Core;

use Delirium\Core\Contract\ApplicationInterface;
use Delirium\Core\Attribute\Module;
use Delirium\Core\Hydrator\ObjectHydrator;
use Delirium\Core\Resolver\PayloadResolver;
use Delirium\DI\ContainerBuilder;
use Delirium\Http\Bridge\SwoolePsrAdapter;
// use Delirium\Http\Contract\RequestInterface;
use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Contract\RouterInterface;
use Delirium\Http\DependencyInjection\Compiler\RoutePass;
use Delirium\Http\Dispatcher\RegexDispatcher;
use Delirium\Http\Message\Request;
use Delirium\Http\Message\Response;
use Delirium\Http\Resolver\ArgumentResolverChain;
use Delirium\Http\Resolver\Request\ContainerServiceResolver;
use Delirium\Http\Resolver\Request\DefaultValueResolver as RequestDefaultValueResolver;
use Delirium\Http\Resolver\Request\ResponseResolver;
use Delirium\Http\Resolver\Request\RouteParameterResolver;
use Delirium\Http\Resolver\Request\ServerRequestResolver;
use Delirium\Http\Resolver\Response\DefaultValueResolver as ResponseDefaultValueResolver;
use Delirium\Http\Resolver\Response\HtmlResolver;
use Delirium\Http\Resolver\Response\JsonResolver;
use Delirium\Http\Resolver\Response\ResponseResolverChain;
use Delirium\Http\Resolver\Response\StreamResolver;
use Delirium\Http\Resolver\Response\XmlResolver;
use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use Delirium\Validation\Adapter\SymfonyValidatorAdapter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use InvalidArgumentException;
use ReflectionClass;

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
            $containerClass = '\Delirium\DI\Cache\ProjectServiceContainer';
            /** @var ContainerInterface $container */
            $container = new $containerClass();
        } else {
            $builder = new ContainerBuilder();
            $container = $builder->getInnerBuilder();
            $container->addCompilerPass(new RoutePass());

            $builder->register(RouteRegistry::class, RouteRegistry::class);
            $builder->register(Router::class, Router::class);

            $container->setAlias('router', Router::class)->setPublic(true);
            $container->setAlias(RouterInterface::class, Router::class)->setPublic(true);

            // PSR-17 Factories
            // PSR-17 Factories
            $builder->register(ResponseFactoryInterface::class, Psr17Factory::class);
            $builder->register(ServerRequestFactoryInterface::class, Psr17Factory::class);
            $builder->register(StreamFactoryInterface::class, Psr17Factory::class);
            $builder->register(UploadedFileFactoryInterface::class, Psr17Factory::class);
            $builder->register(UriFactoryInterface::class, Psr17Factory::class);

            // Bind Delirium Contracts to Implementations (where possible)
            // Note: Request is request-scoped, so usually handled by Resolvers, but we register for completeness/static analysis
            $builder->register(ResponseInterface::class, Response::class);
            // $builder->register(RequestInterface::class, Request::class);

            // Aliases for functional requirement
            // Note: These will point to the *services* registered, not magically the active request instance unless that service is proxying or scoped correctly.
            // But requirement says "->get('request')".
            // Since we don't have request scope in container yet (Swoole context handles it), we map interface names.
            // For now, mapping aliases to Interfaces.
            // For now, mapping aliases to Interfaces.
            $container->setAlias('response', ResponseInterface::class);
            $container->setAlias('request', ServerRequestFactoryInterface::class); // best guess for now or just generic RequestInterface?
            // Actually 'request' usually means the Current Request. Without RequestScope in DI, this is tricky.
            // But per requirement "A chamada ->get('request') retorne uma instância compatível com RequestInterface".
            // We'll trust the ContainerServiceResolver handles injection in controllers.
            // But for explicit $container->get(), we need a service.
            // Let's bind 'response' to ResponseInterface (which is bound to Response class).
            // 'request' is harder. Assuming it means "Factory" or "Empty Request" if outside context.
            $builder->register('request', Request::class); // Register class
            $builder->register('response', Response::class);

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
        // Configure Dispatcher with Argument Resolvers
        $dispatcher = new RegexDispatcher();
        $dispatcher->setContainer($container);

        // We use Nyholm/Psr7 factory implementation strictly
        $psr17Factory = new Psr17Factory();

        // Resolver Chain
        $chainRequest = new ArgumentResolverChain([
            new ServerRequestResolver(),
            new RouteParameterResolver(),
            new PayloadResolver(
                new ObjectHydrator(),
                new SymfonyValidatorAdapter()
            ), // Feature 006
            new ContainerServiceResolver($container),
            new ResponseResolver($psr17Factory, $psr17Factory),
            new RequestDefaultValueResolver(),
        ]);

        $chainResponse = new ResponseResolverChain([
            new JsonResolver($psr17Factory, $psr17Factory),
            new XmlResolver($psr17Factory, $psr17Factory),
            new StreamResolver($psr17Factory, $psr17Factory),
            new HtmlResolver($psr17Factory, $psr17Factory),
            new ResponseDefaultValueResolver($psr17Factory, $psr17Factory),
        ]);

        $dispatcher->setArgumentResolverChain($chainRequest);
        $dispatcher->setResponseResolverChain($chainResponse);
        $router->setDispatcher($dispatcher);

        // 5. Create Adapter
        // We use Nyholm/Psr7 factory implementation strictly
        $psr17Factory = new Psr17Factory();
        $adapter = new SwoolePsrAdapter(
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
            throw new InvalidArgumentException("Module class '$moduleClass' not found.");
        }

        $ref = new ReflectionClass($moduleClass);
        $attributes = $ref->getAttributes(Module::class);

        if (empty($attributes)) {
            throw new InvalidArgumentException("Class '$moduleClass' is not annotated with #[AppModule].");
        }

        /** @var Module $module */
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
