<?php

namespace Pendenga\File;

/**
 * Interface ManifestInterface
 * @package Pendenga\File
 */
interface ManifestInterface
{
    /**
     * @param $manifest_file_name
     * @return mixed
     * @throws FileException
     */
    public function load($manifest_file_name);

    /**
     * Validate that all the files are found in the manifest.
     * @param $file_dir
     * @return mixed
     * @throws FileException
     */
    public function validateFiles($file_dir);

    /**
     * Validate that one file matches the manifest.
     * @param $file_dir
     * @param $file_name
     * @return mixed
     * @throws FileException
     */
    public function validateFile($file_dir, $file_name);
}
