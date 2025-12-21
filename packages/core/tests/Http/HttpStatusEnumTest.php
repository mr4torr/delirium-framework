<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Http;

use Delirium\Core\Http\HttpStatusEnum;
use PHPUnit\Framework\TestCase;

class HttpStatusEnumTest extends TestCase
{
    public function testCodeMethodReturnsIntValue(): void
    {
        $this->assertSame(200, HttpStatusEnum::Ok->code());
        $this->assertSame(404, HttpStatusEnum::NotFound->code());
        $this->assertSame(500, HttpStatusEnum::InternalServerError->code());
    }

    public function testReasonPhraseMethodReturnsString(): void
    {
        $this->assertSame('OK', HttpStatusEnum::Ok->reasonPhrase());
        $this->assertSame('Not Found', HttpStatusEnum::NotFound->reasonPhrase());
        $this->assertSame('Internal Server Error', HttpStatusEnum::InternalServerError->reasonPhrase());
    }

    public function testEnumValuesMatchStandardStatusCodes(): void
    {
        $this->assertSame(201, HttpStatusEnum::Created->value);
        $this->assertSame(400, HttpStatusEnum::BadRequest->value);
    }
}
