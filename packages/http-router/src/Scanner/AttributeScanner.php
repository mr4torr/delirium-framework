<?php

declare(strict_types=1);

namespace Delirium\Http\Scanner;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\RouteAttribute;
use Delirium\Http\RouteRegistry;
use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Reloaded\Coroutine\Coroutine; // Assuming we might use some async stuff or just scan synchronously. Scan is usually sync at boot.

class AttributeScanner
{
    public function __construct(
        private RouteRegistry $registry
    ) {
    }

    public function scanDirectory(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassFromFile($file->getRealPath());
            if ($className) {
                // Determine if we should require it. 
                // If using composer autoload, class_exists should trigger load.
                // But we need to know the class name first.
                require_once $file->getRealPath();
                $this->scanClass($className);
            }
        }
    }

    private function getClassFromFile(string $path): ?string
    {
        $contents = file_get_contents($path);
        $tokens = token_get_all($contents);
        $namespace = '';
        $class = '';
        
        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $namespace .= '\\' . $tokens[$j][1];
                    } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j] === '{') {
                        $class = $tokens[$i + 2][1];
                    }
                }
            }
        }
        
        // This is a very simplified parser. 
        // A better approach is using `nicmart/string-template` or `roave/better-reflection` but we want zero deps for now.
        // Let's use a simpler regex approach which is often robust enough for standard PSR-4 files.
        
        if (preg_match('/namespace\s+(.+?);/', $contents, $matches)) {
            $namespace = $matches[1];
        }
        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $class = $matches[1];
        }
        
        return $namespace && $class ? $namespace . '\\' . $class : null;
    }
    
    public function scanClass(string $className): void
    {
        if (!class_exists($className)) {
            return;
        }

        $ref = new ReflectionClass($className);
        
        // Check for #[Controller]
        $controllerAttrs = $ref->getAttributes(Controller::class);
        if (empty($controllerAttrs)) {
            return;
        }
        
        /** @var Controller $controllerInstance */
        $controllerInstance = $controllerAttrs[0]->newInstance();
        $prefix = rtrim($controllerInstance->prefix, '/');

        foreach ($ref->getMethods() as $method) {
            $attributes = $method->getAttributes(RouteAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);
            
            foreach ($attributes as $attr) {
                /** @var RouteAttribute $routeAttr */
                $routeAttr = $attr->newInstance();
                
                foreach ($routeAttr->methods as $httpMethod) {
                    $path = $prefix . '/' . ltrim($routeAttr->path, '/');
                    // Normalize path
                    if ($path !== '/') {
                        $path = rtrim($path, '/');
                    }
                    
                    $this->registry->addRoute(
                        $httpMethod, 
                        $path, 
                        [$className, $method->getName()]
                    );
                }
            }
        }
    }
}
