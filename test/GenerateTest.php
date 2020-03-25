<?php

namespace Pendenga\File\Test;

use Pendenga\File\ColumnMap;
use Pendenga\File\Directory;
use Pendenga\File\FileNotFoundException;
use Pendenga\File\Generate;
use Pendenga\File\Ini;
use Pendenga\File\Manifest;
use Pendenga\File\ManifestWriter;
use Pendenga\File\Scan;
use Pendenga\Log\EchoLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

include_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class GenerateTest
 * @package Pendenga\File\Test
 */
class GenerateTest extends TestCase
{
    /**
     * @var Directory
     */
    protected $dir;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $tmp_dir;

    /**
     * @throws FileNotFoundException
     */
    public function setup(): void
    {
        $this->logger = new NullLogger(); // change to EchoLogger for debugging
        $this->tmp_dir = Ini::get('TMP_DIRECTORY');
        $this->dir = new Directory(new Scan(), $this->logger);
        $this->dir->deleteFiles($this->tmp_dir);
    }

    /**
     * Generate multiple files in the same directory
     */
    public function testGenerateCheckAll()
    {
        $manifest = $this->tmp_dir . '/manifest.csv';

        // delete from temp dir
        $dir = new Directory(new Scan(), $this->logger);
        $dir->deleteFiles($this->tmp_dir);
        $this->assertEquals([], $dir->files($this->tmp_dir));

        // generate files, and manifest
        $gen = new Generate($this->logger);
        $gen->setOption('file_base_dir', $this->tmp_dir)
            ->setOption('min_files', 3)
            ->setOption('max_files', 3)
            ->setManifest(new ManifestWriter($manifest, $this->logger))
            ->files();
        $this->logger->debug('files created', $dir->files($this->tmp_dir));
        $this->assertEquals(4, count($dir->files($this->tmp_dir)));

        $this->assertTrue(true);

        try {
            $chk = new Manifest(new ColumnMap(), $this->logger);
            $chk->load($manifest);
            $results = $chk->validateFiles($this->tmp_dir, new Scan());
            $this->logger->debug(__METHOD__, $results);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Generate multiple subdirectories of files
     * TODO: ManifestWriter doesn't support the multiple directories yet
     */
    public function testGenerateDirs()
    {
        // $manifest = $this->tmp_dir . '/manifest.csv';

        // delete from temp dir
        $dir = new Directory(new Scan(), $this->logger);
        $dir->deleteFiles($this->tmp_dir);
        $dir->deleteSubDirs($this->tmp_dir);
        $this->assertEquals([], $dir->files($this->tmp_dir));

        // generate files, and manifest
        $gen = new Generate($this->logger);
        $gen->setOption('file_base_dir', $this->tmp_dir)
            ->setOption('min_files', 3)
            ->setOption('max_files', 3)
            ->setOption('max_dirs', 5)
            ->setOption('min_dirs', 5)
        //    ->setManifest(new ManifestWriter($manifest, $this->logger))
            ->files();
        $this->logger->debug('files created', $dir->files($this->tmp_dir));
        $this->assertEquals(15, count($dir->files($this->tmp_dir)));

        // try {
        //     $chk = new Manifest(new ColumnMap(), $this->logger);
        //     $chk->load($manifest);
        //     $results = $chk->validateFiles($this->tmp_dir, new Scan());
        //     $this->logger->debug(__METHOD__, $results);
        // } catch (\Exception $e) {
        //     $this->logger->error($e->getMessage());
        // }
    }
}
