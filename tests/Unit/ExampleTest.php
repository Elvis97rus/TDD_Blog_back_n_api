<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function is_string_returned_by_get_name_method()
    {
        $str = Str::random(12);
        $this->assertIsString($str);
    }
}
