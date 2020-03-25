<?php

namespace Pendenga\File\Test;

use Pendenga\File\Directory;
use Pendenga\File\FileException;
use Pendenga\File\Ini;
use Pendenga\File\Scan;
use Pendenga\Log\EchoLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class DirectoryTest
 * @package Pendenga\File\Test
 */
class DirectoryTest extends TestCase
{
    /**
     * @var Directory
     */
    protected $dir;

    /**
     * @var string
     */
    protected $tmp_dir;

    /**
     * @throws \Pendenga\File\FileNotFoundException
     */
    public function setup(): void
    {
        $logger = new NullLogger(); // change to EchoLogger for debugging
        $this->tmp_dir = Ini::get('TMP_DIRECTORY');
        $this->dir = new Directory(new Scan($logger), $logger);
        $this->dir->deleteFiles($this->tmp_dir);
        $this->dir->deleteSubDirs($this->tmp_dir);
    }

    public function testDeleteDirs()
    {
        // create a directory, check
        $dir_path = $this->tmp_dir . '/subdir';
        mkdir($dir_path);
        $this->assertEquals([$dir_path], $this->dir->directories($this->tmp_dir));

        // delete, check again
        $this->assertEquals(1, $this->dir->deleteSubDirs($this->tmp_dir));
        $this->assertEquals([], $this->dir->directories($this->tmp_dir));
    }

    public function testDeleteFiles()
    {
        // create a file, check
        $file_path = $this->tmp_dir . '/unit_test.txt';
        touch($file_path);
        $this->assertEquals([$file_path], $this->dir->files($this->tmp_dir));

        // delete, check again
        $this->assertEquals(1, $this->dir->deleteFiles($this->tmp_dir));
        $this->assertEquals([], $this->dir->files($this->tmp_dir));
    }

    public function testDirectories()
    {
        // create a directory, check
        $dir_path = $this->tmp_dir . '/subdir';
        mkdir($dir_path);
        $this->assertEquals([$dir_path], $this->dir->directories($this->tmp_dir));
    }

    public function testFiles()
    {
        // create a file, check
        $file_path = $this->tmp_dir . '/unit_test.txt';
        touch($file_path);
        $this->assertEquals([$file_path], $this->dir->files($this->tmp_dir));
    }

    /**
     * @throws FileException
     */
    public function testCheckEmpty()
    {
        $this->dir->checkEmpty($this->tmp_dir);
        $this->assertTrue(true); // no exceptions
    }

    /**
     * @throws FileException
     */
    public function testCheckEmptyException()
    {
        // create a file, check
        $file_path = $this->tmp_dir . '/unit_test.txt';
        touch($file_path);
        $this->assertEquals([$file_path], $this->dir->files($this->tmp_dir));

        // fail not empty
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Directory not empty: /tmp/file');
        $this->dir->checkEmpty($this->tmp_dir);
    }
}
