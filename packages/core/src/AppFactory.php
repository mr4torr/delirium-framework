<?php

declare(strict_types=1);

namespace Delirium\Core;

use Delirium\Core\Container\ContainerFactory;
use Delirium\Core\Contract\ApplicationInterface;
use Delirium\Core\Hydrator\ObjectHydrator;
use Delirium\Core\Resolver\PayloadResolver;
use Delirium\Http\Bridge\SwoolePsrAdapter;
// use Delirium\Http\Contract\RequestInterface;

use Delirium\Http\Dispatcher\RegexDispatcher;
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
use Delirium\Validation\Adapter\SymfonyValidatorAdapter;
use Nyholm\Psr7\Factory\Psr17Factory;

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

        // 2. Initialize Container (Cached or New) via Factory
        $factory = new ContainerFactory();
        $container = $factory->create($moduleClass, $appOptions);

        $router = $container->get(Router::class);
        $router->setContainer($container);

        // Configure Dispatcher with Argument Resolvers
        $dispatcher = new RegexDispatcher();
        $dispatcher->setContainer($container);

        // We use Nyholm/Psr7 factory implementation strictly
        $psr17Factory = new Psr17Factory();

        // Resolver Chain
        $chainRequest = new ArgumentResolverChain([
            new ServerRequestResolver(),
            new RouteParameterResolver(),
            new PayloadResolver(new ObjectHydrator(), new SymfonyValidatorAdapter()), // Feature 006
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
            $psr17Factory, // UploadedFileFactory
        );

        // 6. Create Application
        return new Application($container, $appOptions, $router, $adapter);
    }
}
