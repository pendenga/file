<?php

namespace Pendenga\File\Test;

use Pendenga\File\FileNotFoundException;
use Pendenga\File\Ini;
use PHPUnit\Framework\TestCase;

/**
 * Class IniTest
 * @package Pendenga\File
 */
class IniTest extends TestCase
{
    /**
     * @throws FileNotFoundException
     */
    public function testSection()
    {
        $this->assertEquals(['CONFIG_KEY' => 'config_value'], Ini::section('unit test'));
    }

    /**
     * @throws FileNotFoundException
     */
    public function testSectionException()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('ini file not found');
        $this->assertEquals(['CONFIG_KEY' => 'config_value'], Ini::section('unit test', 'not_there.ini'));
    }

    /**
     * @throws FileNotFoundException
     */
    public function testGet()
    {
        $this->assertEquals('config_value', Ini::get('CONFIG_KEY'));
    }

    /**
     * @throws FileNotFoundException
     */
    public function testGetException()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('ini file not found');
        $this->assertEquals('config_value', Ini::get('CONFIG_KEY', 'not_there.ini'));
    }

    /**
     * @throws FileNotFoundException
     */
    public function testFindFile() {
        $this->assertEquals('/../config.ini', substr(Ini::findFile(), -14));
    }

    /**
     * @throws FileNotFoundException
     */
    public function testFindFileException() {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('ini file not found');
        Ini::findFile('not_there.ini');
    }
}
