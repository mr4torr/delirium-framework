<?php

declare(strict_types=1);

namespace Delirium\Core;

class AppOptions
{
    /** @var array<class-string, object> */
    private array $options = [];

    public function __construct(object ...$options)
    {
        foreach ($options as $option) {
            $this->options[$option::class] = $option;
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $optionClass
     * @return T|null
     */
    public function get(string $optionClass): ?object
    {
        return $this->options[$optionClass] ?? null;
    }
}
