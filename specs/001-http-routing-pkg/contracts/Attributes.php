<?php

namespace Delirium\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public function __construct(
        public readonly string $prefix = '/'
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class Get
{
    public function __construct(
        public readonly string $path = '/'
    ) {}
}

// ... Post, Put, etc follow same pattern
