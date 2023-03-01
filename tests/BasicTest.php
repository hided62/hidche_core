<?php

use \PHPUnit\Framework\TestCase;

final class BasicTest extends TestCase{
    public function testHelloWorld(){
        $this->assertEquals(1+1, 2);
    }
}