# Quickstart: Building the Binary

## Prerequisites
- Docker or Linux environment.
- Composer installed.

## Setup
```bash
composer require --dev crazywhalecc/static-php-cli
```

## Usage
```bash
# 1. Compile everything
bin/compile

# Output will be in build/
ls -lh build/
# build/delirium.phar
# build/delirium (executable binary)
```

## Running the Binary
```bash
./build/delirium server:start
```
