<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Foundation\Cache;

use Delirium\Core\Console\Contract\RegenerationListenerInterface;
use Delirium\Core\Foundation\Cache\RegenerationRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Class RegenerationRegistryTest
 */
class RegenerationRegistryTest extends TestCase
{
    private RegenerationRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new RegenerationRegistry();
    }

    /**
     * Test that listeners can be registered and retrieved.
     */
    public function testRegisterAndGetListeners(): void
    {
        $listener = $this->createMock(RegenerationListenerInterface::class);

        $this->registry->register($listener);

        $this->assertCount(1, $this->registry->getListeners());
        $this->assertSame($listener, $this->registry->getListeners()[0]);
    }
}
