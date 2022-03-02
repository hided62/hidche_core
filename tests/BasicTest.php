<?php
class BasicTest extends PHPUnit\Framework\TestCase{
    public function testHelloWorld(){
        $this->assertEquals(1+1, 2);
    }
}