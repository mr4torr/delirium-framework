<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Integration;

use Delirium\Core\Foundation\AliasLoader;
use PHPUnit\Framework\TestCase;

final class AliasRegistrationTest extends TestCase
{
    public function test_alias_functionality_end_to_end(): void
    {
        $loader = new AliasLoader();

        // Register an alias
        $loader->alias('MyTestAlias', self::class);

        // Activate aliases
        $loader->register();

        // Verify the alias works
        $this->assertTrue(class_exists('MyTestAlias'));

        // Verify that MyTestAlias is recognized as an alias for the test class
        $reflection = new \ReflectionClass('MyTestAlias');
        $this->assertSame(self::class, $reflection->getName());
    }

    public function test_multiple_aliases_work_together(): void
    {
        $loader = new AliasLoader();

        // Register multiple aliases
        $loader->alias('FirstTestAlias', self::class);
        $loader->alias('SecondTestAlias', TestCase::class);

        // Activate all aliases
        $loader->register();

        // Both should work
        $this->assertTrue(class_exists('FirstTestAlias'));
        $this->assertTrue(class_exists('SecondTestAlias'));
    }

    public function test_alias_integration_with_cache_structure(): void
    {
        // Simulate loading from cache
        $cacheManifest = [
            'CachedAlias' => self::class,
            'AnotherCachedAlias' => TestCase::class,
        ];

        $loader = new AliasLoader();
        $loader->loadFromManifest($cacheManifest);
        $loader->register();

        // All aliases from cache should be available
        $this->assertTrue(class_exists('CachedAlias'));
        $this->assertTrue(class_exists('AnotherCachedAlias'));
    }
}
