<?php

namespace Pendenga\File\Test;

use Pendenga\File\Properties;
use PHPUnit\Framework\TestCase;

/**
 * Class PropertiesTest
 * @package Pendenga\File\Test
 */
class PropertiesTest extends TestCase
{
    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function testBytes()
    {
        $prop = new Properties(__DIR__ . '/files/properties.txt');
        $this->assertEquals(83, $prop->bytes());
    }

    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function testChecksum() {
        $prop = new Properties(__DIR__ . '/files/properties.txt');
        $this->assertEquals('5674f664e06cce29f5a2600a8a0318d1', $prop->checksum());
    }

    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function testExists()
    {
        $prop = new Properties(__DIR__ . '/files/properties.txt');
        $this->assertTrue($prop->exists());
    }

    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function testLines()
    {
        $prop = new Properties(__DIR__ . '/files/properties.txt');
        $this->assertEquals(2, $prop->lines());
    }
}
