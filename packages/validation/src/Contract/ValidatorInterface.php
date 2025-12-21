<?php

declare(strict_types=1);

namespace Delirium\Validation\Contract;

interface ValidatorInterface
{
    /**
     * Validates a value against a set of constraints or based on its metadata mapping.
     *
     * @param mixed $value The value to validate
     * @return array<string, list<string>> An array of errors where key is property/path and value is list of messages. Empty if valid.
     */
    public function validate(mixed $value): array;
}
