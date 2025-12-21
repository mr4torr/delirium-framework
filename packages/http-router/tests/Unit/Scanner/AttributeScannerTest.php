<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Unit\Scanner;

use Delirium\Http\RouteRegistry;
use Delirium\Http\Scanner\AttributeScanner;
use PHPUnit\Framework\TestCase;

class AttributeScannerTest extends TestCase
{
    private AttributeScanner $scanner;
    private RouteRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RouteRegistry::class);
        $this->scanner = new AttributeScanner($this->registry);
    }

    public function testScansComplexFixtures(): void
    {
        $fixtureDir = __DIR__ . '/../../Fixtures/Complex';
        
        // Use reflection to access private getClassFromFile or just test public scanDirectory
        // Since scanDirectory actually requires the file and instantiates dependencies,
        // and our fixtures rely on Attribute classes being loaded, we should ensure autoloading works.
        // For unit testing internal method 'getClassFromFile', we might need reflection or expose it.
        // Let's assume we want to test that getClassFromFile correctly identifies the classes.
        
        $ref = new \ReflectionClass($this->scanner);
        $method = $ref->getMethod('getClassFromFile');
        $method->setAccessible(true);

        // Test Simple Complex
        $class = $method->invoke($this->scanner, $fixtureDir . '/ComplexController.php');
        $this->assertEquals('Delirium\Http\Tests\Fixtures\Complex\ComplexController', $class);

        // Test Bracketed Namespace
        $class = $method->invoke($this->scanner, $fixtureDir . '/BracketedController.php');
        $this->assertEquals('Delirium\Http\Tests\Fixtures\Complex\BracketedController', $class);

        // Test Ignored Interface (getClassFromFile should return null or ignore it?)
        // The previous regex implementation might have picked it up if searching for 'class' keyword too broadly.
        // The new token scanner should strictly find T_CLASS.
        // Interfaces use T_INTERFACE.
        $class = $method->invoke($this->scanner, $fixtureDir . '/IgnoredInterface.php');
        $this->assertNull($class, 'Interface should be ignored');
    }
}
