<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\Math;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    // public function testBasicTest()
    // {
    //     $this->assertTrue(true);
    // }

    public function test_it_can_add_two_numbers()
    {
        $math = new Math();
        $result = $math->add(5, 3);

        $this->assertSame(8, $result);
        $this->assertNotNull($result);
        $this->assertIsNumeric($result);
        
    }
}
