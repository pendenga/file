<?php

namespace Pendenga\File\Test;

use Pendenga\File\Directory;
use Pendenga\File\FileException;
use Pendenga\File\FileNotFoundException;
use Pendenga\File\ColumnMap;
use Pendenga\File\Manifest;
use Pendenga\File\Scan;
use Pendenga\Log\EchoLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Pendenga\File\Ini;

include_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ManifestTest
 * @package Pendenga\File\Test
 */
class ManifestTest extends TestCase
{
    /**
     * @var Directory
     */
    protected $dir;

    /**
     * @var Manifest
     */
    protected $manifest;

    /**
     * @var string
     */
    protected $manifest_file;
    protected $tmp_dir;

    /**
     * @throws FileException
     * @throws FileNotFoundException
     */
    public function setup(): void
    {
        $logger = new NullLogger(); // change to EchoLogger for debugging
        $logger->debug('-- start setup --');
        $this->tmp_dir = Ini::get('TMP_DIRECTORY');
        $this->dir = new Directory(new Scan(), $logger);
        $this->dir->deleteFiles($this->tmp_dir);

        // create a files, check
        $file_paths = [
            $this->tmp_dir . '/unit_test_one.txt',
            $this->tmp_dir . '/unit_test_two.txt',
        ];
        foreach ($file_paths as $file_path) {
            touch($file_path);
        }
        $this->assertEquals($file_paths, $this->dir->files($this->tmp_dir));

        // create manifest
        $this->manifest_file = $this->tmp_dir . '/manifest.csv';
        $this->manifest = new Manifest(new ColumnMap(), $logger);
        $logger->debug('-- finish setup --');
    }

    /**
     * @throws \Pendenga\File\FileException
     */
    public function testAll()
    {
        file_put_contents($this->manifest_file, "name\nunit_test_one.txt\nunit_test_two.txt");
        $this->manifest->load($this->manifest_file);

        $results = $this->manifest->validateFiles($this->tmp_dir, new Scan());
        $this->assertEquals([], $results);
    }

    /**
     * @throws \Pendenga\File\FileException
     */
    public function testSingle()
    {
        file_put_contents($this->manifest_file, "name\nunit_test_one.txt\nunit_test_two.txt");
        $this->manifest->load($this->manifest_file);

        $full_path = $this->tmp_dir . '/unit_test_one.txt';
        $this->assertEquals([], $this->manifest->validateFile($this->tmp_dir, $full_path));
    }

    /**
     * @throws \Pendenga\File\FileException
     */
    public function testSingleRelative()
    {
        file_put_contents($this->manifest_file, "name\nunit_test_one.txt\nunit_test_two.txt");
        $this->manifest->load($this->manifest_file);

        $relative_path = 'unit_test_one.txt';
        $this->assertEquals([], $this->manifest->validateFile($this->tmp_dir, $relative_path));
    }

    /**
     * @throws \Pendenga\File\FileException
     */
    public function testSingleNotFound()
    {
        $test_path = $this->tmp_dir . '/not_found.txt';

        // manifest needed, but not used
        file_put_contents($this->manifest_file, "name\nirrelevant.txt");
        $this->manifest->load($this->manifest_file);

        $this->assertEquals(
            ["File not found: {$test_path}" => [$test_path]],
            $this->manifest->validateFile($this->tmp_dir, $test_path)
        );
    }

    /**
     * @throws \Pendenga\File\FileException
     */
    public function testSingleRewriteManifest()
    {
        $test_path = $this->tmp_dir . '/unit_test_one.txt';

        // create invalid manifest
        file_put_contents($this->manifest_file, "name\nunit_test_two.txt");
        $this->manifest->load($this->manifest_file);

        $this->assertEquals(
            ['File missing from manifest' => [$test_path]],
            $this->manifest->validateFile($this->tmp_dir, $test_path)
        );

        // create and load good manifest
        file_put_contents($this->manifest_file, "name\nunit_test_one.txt");
        $this->manifest->load($this->manifest_file);

        $this->assertEquals([], $this->manifest->validateFile($this->tmp_dir, $test_path));
    }

    /**
     * @throws \Pendenga\File\FileException
     */
    public function testSingleNotInManifest()
    {
        $test_path = $this->tmp_dir . '/unit_test_two.txt';

        // create different manifest
        file_put_contents($this->manifest_file, "name\nunit_test_one.txt");
        $this->manifest->load($this->manifest_file);

        $this->assertEquals(
            ['File missing from manifest' => [$test_path]],
            $this->manifest->validateFile($this->tmp_dir, $test_path)
        );
    }

    /**
     * @throws \Pendenga\File\FileException
     */
    public function testSingleFailedCheck()
    {
        $test_path = $this->tmp_dir . '/unit_test_one.txt';

        // create different manifest with checksum
        file_put_contents($this->manifest_file, "name,check\nunit_test_one.txt," . md5_file($test_path));
        $this->manifest->load($this->manifest_file);

        // write different contents
        file_put_contents($test_path, 'not nothing');

        // test against previous manifest, checksum shouldn't match
        $this->assertEquals(
            ['checksum failed' => [$test_path]],
            $this->manifest->validateFile($this->tmp_dir, $test_path)
        );
    }
}
