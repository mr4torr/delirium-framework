<?php

declare(strict_types=1);

namespace Delirium\Core\Options;

class DebugOptions
{
    public function __construct(
        public readonly bool $debug = false,
        public readonly bool $liveReload = false,
        public readonly array $watchDirs = ['src', 'packages'],
    ) {
    }
}
