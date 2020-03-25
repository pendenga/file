<?php

use Pendenga\File\ColumnMap;
use Pendenga\File\Directory;
use Pendenga\File\Manifest;
use Pendenga\File\ManifestWriter;
use Pendenga\File\Scan;
use Pendenga\Log\EchoLogger;
use Psr\Log\NullLogger;
use Pendenga\File\Ini;
use Pendenga\File\Generate;

include_once __DIR__ . '/../vendor/autoload.php';

$logger = new NullLogger(); // change to EchoLogger for debugging
try {
    $manifest = Ini::get('TMP_DIRECTORY') . '/manifest.csv';

    // delete from temp dir
    // NOTE: if you don't delete, any existing files will show up as exceptions
    $dir = new Directory(new Scan(), $logger);
    $dir->deleteFiles(Ini::get('TMP_DIRECTORY'));

    // generate new files
    $gen = new Generate($logger);
    $gen->setOption('file_base_dir', Ini::get('TMP_DIRECTORY'))
        ->setManifest(new ManifestWriter($manifest))
        ->files();

    // check the manifest
    $chk = new Manifest(new ColumnMap(), $logger);
    $chk->load($manifest);
    $results = $chk->validateFiles(Ini::get('TMP_DIRECTORY'), new Scan());

    // print results
    if (count($results) == 0) {
        print "All files were validated with no exceptions\n";
        print "See files in " . Ini::get('TMP_DIRECTORY') . "\n";
    } else {
        print "Manifest found the following exceptions: ";
        print_r($results);
    }
} catch (\Exception $e) {
    $logger->error($e->getMessage());
}
