<?php

namespace Pendenga\File\Test;

use Pendenga\File\Scan;
use Pendenga\Log\EchoLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class ScanTest
 * @package Pendenga\File\Test
 */
class ScanTest extends TestCase
{
    protected $scan;

    public function setup(): void
    {
        $logger = new NullLogger(); // change to EchoLogger for debugging
        $this->scan = new Scan($logger);
    }

    public function testDirectories()
    {
        $this->assertEquals([__DIR__ . '/files/other'], $this->scan->directories(__DIR__ . '/files'));
        $this->assertEquals([__DIR__ . '/files'], $this->scan->directories(__DIR__));
    }

    public function testFiles()
    {
        $this->assertEquals(
            [
                __DIR__ . '/files/properties.txt',
            ],
            $this->scan->files(__DIR__ . '/files', '*.txt')
        );
    }

    public function testScan()
    {
        $this->assertEquals(
            [
                __DIR__ . '/files/properties.txt' => 1,
            ],
            $this->scan->regex('/function/', $this->scan->files(__DIR__ . '/files', '*.txt'))
        );
    }
}
