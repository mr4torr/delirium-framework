<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Delirium\Core\AppFactory;

// Bootstrap simple
AppFactory::create(App\AppModule::class)->listen();



// use Delirium\Core\AppOptions;
// use Delirium\Core\Options\CorsOptions;

// Bootstrap with options
// AppFactory::create(App\AppModule::class, new AppOptions(
//     new CorsOptions(
//         allowOrigins: ['*'],
//         allowMethods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
//         allowHeaders: ['Content-Type', 'Authorization']
//     )
// ))->listen(9501);
