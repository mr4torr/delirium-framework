<?php

declare(strict_types=1);

namespace Delirium\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapRequestPayload
{
    /**
     * @param string[] $serializationContext Options for serialization/hydration if needed.
     * @param string[] $validationGroups Groups for validation.
     */
    public function __construct(
        public array $serializationContext = [],
        public array $validationGroups = [],
    ) {}
}
