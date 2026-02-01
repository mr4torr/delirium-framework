<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Unit\Module;

use Delirium\Core\Attribute\Module;
use Delirium\Core\Module\ModuleScanner;
use Delirium\DI\ContainerBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

// --- Fixtures ---

#[Module]
class EmptyModule {}

#[Module(
    controllers: ['StdClass'],
    providers: ['stdClass']
)]
class SimpleModule {}

#[Module(
    imports: [SimpleModule::class]
)]
class ParentModule {}

#[Module(
    imports: [CycleModuleA::class]
)]
class CycleModuleA {}

class NonAttributeModule {}

class ModuleScannerTest extends TestCase
{
    public function testScanRegistersComponents(): void
    {
        $builder = new ContainerBuilder();
        $scanner = new ModuleScanner();

        $scanner->scan(SimpleModule::class, $builder);

        // Check inner builder for definitions, or use public alias if available.
        // Delirium Builder doesn't expose 'has' directly on wrapper?
        // It exposes getInnerBuilder().
        $this->assertTrue($builder->getInnerBuilder()->has('StdClass'));
        $this->assertTrue($builder->getInnerBuilder()->has('stdClass'));
    }

    public function testScanRecursesImports(): void
    {
        $builder = new ContainerBuilder();
        $scanner = new ModuleScanner();

        $scanner->scan(ParentModule::class, $builder);

        // Parent imports SimpleModule, so SimpleModule deps should be registered
        $this->assertTrue($builder->getInnerBuilder()->has('StdClass'));
    }

    public function testScanHandlesCyclesGracefully(): void
    {
        $builder = new ContainerBuilder();
        $scanner = new ModuleScanner();

        // Should not crash (recursive infinite loop)
        $scanner->scan(CycleModuleA::class, $builder);

        $this->assertTrue(true, 'Cycle detection prevented infinite recursion');
    }

    public function testInvalidClassThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Module class 'NonExistent' not found.");

        $builder = new ContainerBuilder();
        $scanner = new ModuleScanner();
        $scanner->scan('NonExistent', $builder);
    }

    public function testMissingAttributeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("is not annotated with #[Module]");

        $builder = new ContainerBuilder();
        $scanner = new ModuleScanner();
        $scanner->scan(NonAttributeModule::class, $builder);
    }
}
