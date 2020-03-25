<?php

namespace Pendenga\File\Test;

use Pendenga\File\Batch;
use Pendenga\File\Directory;
use Pendenga\File\Generate;
use Pendenga\File\Ini;
use Pendenga\File\Scan;
use Pendenga\Log\EchoLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class BatchTest
 * @package Pendenga\File\Test
 */
class BatchTest extends TestCase
{
    /**
     * @var Batch
     */
    protected $batch;

    /**
     * @var Directory
     */
    protected $dir;

    /**
     * @var NullLogger
     */
    protected $logger;

    public function setup():void {
        $scan = new Scan();
        $this->logger = new NullLogger(); // change to EchoLogger for debugging
        $this->dir = new Directory($scan, $this->logger);
        $this->batch = new Batch($scan, $this->logger);
    }

    public function testBytes() {
        $this->assertEquals(83, $this->batch->bytes(__DIR__ . '/files'));
    }

    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function testBatch() {
        $tmp_dir = Ini::get('TMP_DIRECTORY');

        // delete from temp dir
        $this->dir->deleteFiles($tmp_dir);
        $this->dir->deleteSubDirs($tmp_dir);
        $this->assertEquals([], $this->dir->files($tmp_dir));

        // generate 5 folders, 3 files in each
        $gen = new Generate($this->logger);
        $gen->setOption('file_base_dir', $tmp_dir)
            ->setOption('min_files', 3)
            ->setOption('max_files', 3)
            ->setOption('max_dirs', 5)
            ->setOption('min_dirs', 5)
            ->files();
        $this->logger->debug('files created', $this->dir->files($tmp_dir));
        $this->assertEquals(15, count($this->dir->files($tmp_dir)));

        // evaluate how to split the files into 2-3 batches
        $batches = $this->batch->evaluate($tmp_dir);
        $this->assertGreaterThan(1, count($batches));
        $this->assertLessThan(4, count($batches));
        $this->assertEquals(['dirs', 'bytes','name'], array_keys($batches[0]));
    }
}
