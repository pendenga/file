<?php

/**
 * Actual executable for a customer, for which I had generated 4000+ folders with 165,000+ files.
 * The target was Box.com and the parent folder was bigger than their 15gb limit for a single download.
 * This script moved the files into 15 batch sub-directories of less than 10GB each.
 * I ended up zipping the 15 directories and uploading 15 files of less than 1GB each.
 */

use Pendenga\File\Batch;
use Pendenga\File\Scan;
use Pendenga\Log\EchoLogger;

include_once __DIR__ . '/../vendor/autoload.php';

$logger = new EchoLogger();
$tmp_dir = '/Users/pendenga/Downloads/customer';

$batch = new Batch(new Scan(), $logger);
$batch->setOption('max_batch_bytes', 10737418240);
$batches = $batch->evaluate($tmp_dir);
print_r($batches);
// $batch->execute($batches);

