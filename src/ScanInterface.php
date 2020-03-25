<?php

namespace Pendenga\File;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface ScanInterface
 * @package Pendenga\File
 */
interface ScanInterface extends LoggerAwareInterface
{
    /**
     * @return array
     */
    public function files($file_dir, $pattern);

    /**
     * Test every line of the $files for the regex. Return the file and number of occurrences.
     * @param string $pattern
     * @param array  $files
     * @return array
     */
    public function regex($pattern, array $files);
}
