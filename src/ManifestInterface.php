<?php

namespace Pendenga\File;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface ManifestInterface
 * @package Pendenga\File
 */
interface ManifestInterface extends LoggerAwareInterface
{
    /**
     * @param $manifest_file_name
     * @return $this
     * @throws FileException
     */
    public function load($manifest_file_name);

    /**
     * Validate that all the files are found in the manifest.
     * @param string        $file_dir
     * @param ScanInterface $scan
     * @return array
     * @throws FileException
     */
    public function validateFiles($file_dir, ScanInterface $scan);

    /**
     * Validate that one file matches the manifest.
     * @param $file_dir
     * @param $file_name
     * @return array
     * @throws FileException
     */
    public function validateFile($file_dir, $file_name);
}
