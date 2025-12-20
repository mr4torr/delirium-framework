<?php

declare(strict_types=1);

namespace Delirium\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Options extends RouteAttribute
{
    public function __construct(string $path = '/')
    {
        parent::__construct($path, ['OPTIONS']);
    }
}
