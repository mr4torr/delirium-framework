<?php

declare(strict_types=1);

namespace Delirium\Compile\Config;

class CompileConfig
{
    /**
     * @param array<string> $paths Directories to include in the PHAR
     * @param array<string> $extensions Required PHP extensions for the binary
     * @param string $outputName Name of the generated binary
     */
    public function __construct(
        public readonly array $paths = ['src', 'packages', 'public'],
        public readonly array $extensions = [
            'ctype', 'iconv', 'dom', 'openssl', 'curl', 'pcntl', 'mbstring',
            'tokenizer', 'xml', 'filter', 'json', 'phar', 'posix', 'zlib', 'swoole'
        ],
        public readonly string $outputName = 'delirium'
    ) {
    }

    public static function createDefault(): self
    {
        return new self();
    }
}
