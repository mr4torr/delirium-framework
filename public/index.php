<?php

declare(strict_types=1);

use Delirium\Core\AppFactory;

// Bootstrap simple
use Delirium\Core\AppOptions;
use Delirium\Core\Options\DebugOptions;

// Default Bootstrap
return AppFactory::create(App\AppModule::class, new AppOptions(
    new DebugOptions(debug: true, liveReload: true)
));



// use Delirium\Core\AppOptions;
// use Delirium\Core\Options\CorsOptions;
// use Delirium\Core\Options\DebugOptions;

// Bootstrap with options
// AppFactory::create(App\AppModule::class, new AppOptions(
//     new DebugOptions(debug: true)
//     new CorsOptions(
//         allowOrigins: ['*'],
//         allowMethods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
//         allowHeaders: ['Content-Type', 'Authorization']
//     )
// ))->listen(9501);
