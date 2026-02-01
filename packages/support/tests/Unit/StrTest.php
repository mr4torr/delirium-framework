<?php

declare(strict_types=1);

namespace Delirium\Support\Tests\Unit;

use Delirium\Support\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    public function testRandom()
    {
        $this->assertEquals(16, strlen(Str::random(16)));
        $this->assertNotEquals(Str::random(), Str::random());
    }

    public function testContains()
    {
        $this->assertTrue(Str::contains('Taylor', 'ylo'));
        $this->assertTrue(Str::contains('Taylor', ['ylo', 'xxx']));
        $this->assertFalse(Str::contains('Taylor', 'xxx'));
    }
}
