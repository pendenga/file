<?php

namespace Pendenga\File\Test;

use Pendenga\File\ColumnMap;
use Pendenga\File\FileException;
use Pendenga\Log\EchoLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class ColumnMapTest
 * @package Pendenga\File
 */
class ColumnMapTest extends TestCase
{
    /**
     * @var ColumnMap
     */
    protected $map;

    public function setup(): void {
        $logger = new NullLogger(); // change to EchoLogger for debugging
        $this->map = new ColumnMap($logger);
    }

    /**
     * @throws FileException
     */
    public function testParseHeader()
    {
        $this->map->parseHeader(['file', 'size', 'rows', 'checksum']);
        $this->assertEquals(0, $this->map->index(ColumnMap::NAME));
        $this->assertEquals(1, $this->map->index(ColumnMap::SIZE));
        $this->assertEquals(2, $this->map->index(ColumnMap::ROWS));
        $this->assertEquals(3, $this->map->index(ColumnMap::CHECK));
        $this->assertEquals(['col_name','col_size','col_rows','col_check'], $this->map->columnKeys());
    }

    /**
     * @throws FileException
     */
    public function testParseHeader2()
    {
        $this->map->parseHeader(['count', 'filename', 'nothing', 'bytes']);
        $this->assertEquals(0, $this->map->index(ColumnMap::ROWS));
        $this->assertEquals(1, $this->map->index(ColumnMap::NAME));
        $this->assertEquals(3, $this->map->index(ColumnMap::SIZE));

        // isHeader must match on all the columns, even the ignored ones
        $this->assertTrue($this->map->isHeader(['count', 'filename', 'nothing', 'bytes']));
        $this->assertTrue($this->map->isHeader([0 => 'count', 1 => 'filename', 2 => 'nothing', 3 => 'bytes']));
        $this->assertFalse($this->map->isHeader(['count', 'filename', 'nada', 'bytes']));
        $this->assertFalse($this->map->isHeader(['rows', 'file', 'nada', 'bytes']));
    }

    /**
     * @throws FileException
     */
    public function test2()
    {
        $data = [
            ['col1', 'file', 'col2'],
            ['lary', 'kurly', 'mow'],
        ];

        $this->map->parseHeader($data[0]);
        $this->assertEquals(1, $this->map->index(ColumnMap::NAME));
        $this->assertEquals('kurly', $this->map->value(ColumnMap::NAME, $data[1]));
        $this->assertEquals('kurly', $data[1][$this->map->index(ColumnMap::NAME)]);
    }
}
