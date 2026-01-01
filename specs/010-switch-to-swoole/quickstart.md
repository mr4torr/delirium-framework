# Quickstart: Running with Swoole

**Prerequisites**:
- PHP 8.4+
- Swoole Extension (`ext-swoole`) installed and enabled.

## Installation

```bash
pecl install swoole
# Enable extension in php.ini: extension=swoole.so
```

## Running the Server

```bash
php bin/console server:start
```

*Note: The binary distribution (SPC) will have this built-in.*
