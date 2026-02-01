<?php

declare(strict_types=1);

namespace Delirium\Support\Tests;

use PHPUnit\Framework\TestCase;

class ArchitectureTest extends TestCase
{
    public function testSupportHasZeroInternalDependencies()
    {
        $content = file_get_contents(__DIR__ . '/../composer.json');
        $this->assertNotFalse($content, 'composer.json not found');

        $composerJson = json_decode($content, true);
        $require = $composerJson['require'] ?? [];

        // Support package must NOT depend on any other Delirium package
        foreach (array_keys($require) as $package) {
            if (str_starts_with($package, 'delirium/')) {
                $this->fail("Support package must not depend on other framework packages. Found: $package");
            }
        }

        $this->assertTrue(true);
    }
}
