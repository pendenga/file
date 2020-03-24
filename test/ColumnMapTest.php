<?php

namespace Pendenga\File;

use Pendenga\Log\EchoLogger;
use PHPUnit\Framework\TestCase;

class ColumnMapTest extends TestCase
{
    /**
     * @throws FileException
     */
    public function testParseHeader()
    {
        $map = new ColMap();
        $map->parseHeader(['file', 'size', 'rows']);
        $this->assertEquals(0, $map->index(ColMap::NAME));
        $this->assertEquals(1, $map->index(ColMap::SIZE));
        $this->assertEquals(2, $map->index(ColMap::ROWS));

        $this->assertEquals(['col_name','col_size','col_rows'], $map->columnKeys());
    }

    /**
     * @throws FileException
     */
    public function testParseHeader2()
    {
        $map = new ColMap(new EchoLogger());
        $map->parseHeader(['count', 'filename', 'nothing', 'bytes']);
        $this->assertEquals(0, $map->index(ColMap::ROWS));
        $this->assertEquals(1, $map->index(ColMap::NAME));
        $this->assertEquals(3, $map->index(ColMap::SIZE));

        // isHeader must match on all the columns, even the ignored ones
        $this->assertTrue($map->isHeader(['count', 'filename', 'nothing', 'bytes']));
        $this->assertTrue($map->isHeader([0 => 'count', 1 => 'filename', 2 => 'nothing', 3 => 'bytes']));
        $this->assertFalse($map->isHeader(['count', 'filename', 'nada', 'bytes']));
        $this->assertFalse($map->isHeader(['rows', 'file', 'nada', 'bytes']));
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

        $map = new ColMap(new EchoLogger());
        $map->parseHeader($data[0]);
        $this->assertEquals(1, $map->index(ColMap::NAME));
        $this->assertEquals('kurly', $map->value(ColMap::NAME, $data[1]));
        $this->assertEquals('kurly', $data[1][$map->index(ColMap::NAME)]);
    }
}
