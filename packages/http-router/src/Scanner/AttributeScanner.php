<?php

declare(strict_types=1);

namespace Delirium\Http\Scanner;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\RouteAttribute;
use Delirium\Http\RouteRegistry;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class AttributeScanner
{
    public function __construct(
        private RouteRegistry $registry,
    ) {}

    public function scanDirectory(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $realPath = $file->getRealPath();
            if ($realPath === false) {
                continue;
            }

            $className = $this->getClassFromFile($realPath);
            if ($className) {
                // Determine if we should require it.
                // If using composer autoload, class_exists should trigger load.
                // But we need to know the class name first.
                require_once $realPath;
                $this->scanClass($className);
            }
        }
    }

    private function getClassFromFile(string $path): ?string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }
        $tokens = token_get_all($contents);

        $namespace = '';
        $class = '';

        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            // Namespace detection
            if ($token[0] === T_NAMESPACE) {
                // Advance and capture until ; or {
                for ($j = $i + 1; $j < $count; $j++) {
                    $nextToken = $tokens[$j];

                    if (is_string($nextToken) && (hash_equals(';', $nextToken) || hash_equals('{', $nextToken))) {
                        break;
                    }

                    if (
                        is_array($nextToken)
                        && (
                            $nextToken[0] === T_STRING
                            || $nextToken[0] === T_NAME_QUALIFIED
                            || $nextToken[0] === T_NS_SEPARATOR
                        )
                    ) {
                        $namespace .= $nextToken[1];
                    }
                }
            }

            // Class detection
            if ($token[0] === T_CLASS) {
                // Ensure it's not ::class or new class
                // Check previous significant token? (Simplification: just get next string)

                // Advance to find class name string
                for ($j = $i + 1; $j < $count; $j++) {
                    $nextToken = $tokens[$j];
                    if (is_string($nextToken) && hash_equals('{', $nextToken)) {
                        // Reached body before name? Anon class?
                        break;
                    }

                    if (is_array($nextToken) && $nextToken[0] === T_STRING) {
                        $class = $nextToken[1];
                        break;
                    }
                }
            }
        }

        if ($class === '') {
            return null;
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }

    public function scanClass(string $className, string $modulePrefix = ''): void
    {
        if (!class_exists($className)) {
            return;
        }

        $ref = new ReflectionClass($className);

        // Check for #[Controller]
        $controllerAttrs = $ref->getAttributes(Controller::class);
        if ($controllerAttrs === []) {
            return;
        }

        $controllerInstance = $controllerAttrs[0]->newInstance();
        $prefix = rtrim($modulePrefix, '/') . '/' . ltrim($controllerInstance->prefix, '/');
        $prefix = rtrim($prefix, '/');

        foreach ($ref->getMethods() as $method) {
            $attributes = $method->getAttributes(RouteAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attr) {
                $routeAttr = $attr->newInstance();

                foreach ($routeAttr->methods as $httpMethod) {
                    $path = $prefix . '/' . ltrim($routeAttr->path, '/');
                    // Normalize path
                    if ($path !== '/') {
                        $path = rtrim($path, '/');
                    }

                    $this->registry->addRoute((string) $httpMethod, $path, [$className, $method->getName()]);
                }
            }
        }
    }
}
