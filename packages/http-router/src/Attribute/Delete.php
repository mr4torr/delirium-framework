<?php

declare(strict_types=1);

namespace Delirium\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Delete extends RouteAttribute
{
    public function __construct(
        string $path = '/',
        ?\Delirium\Http\Enum\ResponseTypeEnum $type = \Delirium\Http\Enum\ResponseTypeEnum::JSON,
        ?int $status = 200,
    ) {
        parent::__construct($path, ['DELETE'], $type, $status);
    }
}
