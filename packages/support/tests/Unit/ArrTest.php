<?php

declare(strict_types=1);

namespace Delirium\Support\Tests\Unit;

use Delirium\Support\Arr;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function testGet()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $this->assertEquals(100, Arr::get($array, 'products.desk.price'));
        $this->assertNull(Arr::get($array, 'products.desk.discount'));
        $this->assertEquals(0, Arr::get($array, 'products.desk.discount', 0));
        $this->assertEquals($array, Arr::get($array, null));
        $this->assertEquals(['price' => 100], Arr::get($array, 'products.desk'));
    }
}
