# Data Model: SPC Compiler

**Feature**: SPC Compiler (011-spc-compiler)

## Entities

### CompileConfig
- **paths**: array (default: `['src', 'config', 'public', 'vendor']`)
- **extensions**: array (list of required exts like `swoole`, `openssl`)
- **output_name**: string (default: `delirium`)

## Classes

### PharBuilder
- **Role**: create the `build/delirium.phar`.
- **Method**: `build(CompileConfig $config): string`

### SpcBuilder
- **Role**: wrapper around `vendor/bin/spc`.
- **Method**: `downloadSources()`
- **Method**: `buildMicro(string $pharPath, CompileConfig $config): string`
