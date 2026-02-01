<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Foundation;

use Delirium\Core\Foundation\AliasLoader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AliasLoaderTest extends TestCase
{
    public function test_alias_registers_mapping(): void
    {
        $loader = new AliasLoader();
        $loader->alias('TestAlias', self::class);

        $aliases = $loader->getAliases();

        $this->assertArrayHasKey('TestAlias', $aliases);
        $this->assertSame(self::class, $aliases['TestAlias']);
    }

    public function test_alias_throws_for_non_existent_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Alias target class [NonExistentClass] does not exist');

        $loader = new AliasLoader();
        $loader->alias('SomeAlias', 'NonExistentClass');
    }

    public function test_register_creates_aliases(): void
    {
        $loader = new AliasLoader();
        $loader->alias('AliasLoaderTestAlias', self::class);
        $loader->register();

        // Verify the alias was created
        $this->assertTrue(class_exists('AliasLoaderTestAlias'));
        $this->assertSame(self::class, 'AliasLoaderTestAlias');
    }

    public function test_register_throws_if_alias_conflicts_with_existing_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot alias [stdClass] as it already exists');

        $loader = new AliasLoader();
        // stdClass already exists
        $loader->alias('stdClass', self::class);
        $loader->register();
    }

    public function test_load_from_manifest(): void
    {
        $loader = new AliasLoader();
        $manifest = [
            'TestManifestAlias' => self::class,
        ];

        $loader->loadFromManifest($manifest);
        $aliases = $loader->getAliases();

        $this->assertArrayHasKey('TestManifestAlias', $aliases);
        $this->assertSame(self::class, $aliases['TestManifestAlias']);
    }

    public function test_register_is_idempotent(): void
    {
        $loader = new AliasLoader();
        $loader->alias('IdempotentAlias', self::class);

        $loader->register();
        $loader->register(); // Should not throw

        $this->assertTrue(class_exists('IdempotentAlias'));
    }
}
