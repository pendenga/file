<?php

namespace Pendenga\File\Test;

use Pendenga\File\Prop;
use PHPUnit\Framework\TestCase;

/**
 * Class PropTest
 * @package Pendenga\File\Test
 */
class PropTest extends TestCase
{
    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function testExists()
    {
        $prop = new Prop(__DIR__ . '/files/prop.txt');
        $this->assertTrue($prop->exists());
    }

    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function testLines()
    {
        $prop = new Prop(__DIR__ . '/files/prop.txt');
        $this->assertEquals(2, $prop->lines());
    }

    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function testBytes()
    {
        $prop = new Prop(__DIR__ . '/files/prop.txt');
        $this->assertEquals(83, $prop->bytes());
    }
}
