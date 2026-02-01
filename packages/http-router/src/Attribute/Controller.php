<?php

declare(strict_types=1);

namespace Delirium\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public function __construct(
        public readonly string $prefix = '/',
    ) {}
}
