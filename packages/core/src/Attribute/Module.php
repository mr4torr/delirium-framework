<?php

declare(strict_types=1);

namespace Delirium\Core\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Module
{
    /**
     * @param array<class-string> $imports List of modules to import.
     * @param array<class-string> $controllers List of controllers to register.
     * @param array<class-string|callable> $providers List of providers (services) to register.
     */
    public function __construct(
        public array $imports = [],
        public array $controllers = [],
        public array $providers = [],
    ) {
    }
}
