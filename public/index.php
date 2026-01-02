<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Delirium\Core\AppFactory::create(
    App\AppModule::class,
    new Delirium\Core\AppOptions(
        new Delirium\Core\Options\DebugOptions(debug: true),
    )
)->listen();
