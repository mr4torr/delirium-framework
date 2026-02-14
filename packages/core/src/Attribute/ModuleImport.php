<?php

declare(strict_types=1);

namespace Delirium\Core\Attribute;

class ModuleImport
{
    public function __construct(
        public string $class,
        public string $path = '',
    ) {
    }
}
