<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Swoole\Http\Server;

echo "Verifying Swoole installation...\n";

if (!extension_loaded('swoole')) {
    echo "ERROR: Swoole extension is not loaded.\n";
    exit(1);
}

echo "Swoole extension loaded. Version: " . swoole_version() . "\n";

if (!class_exists(Server::class)) {
    echo "ERROR: Swoole\Http\Server class not found.\n";
    exit(1);
}

try {
    // Attempt instantiation (port 0 to pick random valid port/avoid binding, or just check existence)
    // Actually, creating a server object usually doesn't bind until start(), but strict mode might.
    // We just want to check the class works.
    $server = new Server('127.0.0.1', 0, SWOOLE_BASE); 
    echo "Successfully instantiated Swoole\Http\Server.\n";
} catch (\Throwable $e) {
    echo "ERROR: Failed to instantiate Swoole Server: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Verification passed.\n";
