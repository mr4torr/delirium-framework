<?php

declare(strict_types=1);

namespace Delirium\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
abstract class RouteAttribute
{
    public function __construct(
        public readonly string $path = '/',
        public readonly array $methods = ['GET'],
        public readonly ?\Delirium\Http\Enum\ResponseTypeEnum $type = \Delirium\Http\Enum\ResponseTypeEnum::JSON,
        public readonly ?int $status = 200
    ) {
    }
}
