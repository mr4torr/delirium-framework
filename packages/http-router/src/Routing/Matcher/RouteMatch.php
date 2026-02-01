<?php

declare(strict_types=1);

namespace Delirium\Http\Routing\Matcher;

readonly class RouteMatch
{
    /**
     * @param mixed $handler The matched route handler (callable, array, string)
     * @param array<string, mixed> $params Extracted route parameters
     */
    public function __construct(
        public mixed $handler,
        public array $params = [],
    ) {}
}
