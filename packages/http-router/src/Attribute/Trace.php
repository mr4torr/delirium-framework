<?php

declare(strict_types=1);

namespace Delirium\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Trace extends RouteAttribute
{
    public function __construct(string $path = '/')
    {
        parent::__construct($path, ['TRACE']);
    }
}
