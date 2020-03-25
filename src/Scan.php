<?php

namespace Pendenga\File;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Scan
 * @package Pendenga\File\Scan
 */
class Scan implements ScanInterface
{
    use LoggerAwareTrait;

    const DIR_TO_AVOID = array(
        '.git',
        'vendor',
    );

    /**
     * Scan constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * NOTE: Does not descend into sub-directories
     * @param string $file_dir
     * @return array
     */
    public function directories($file_dir)
    {
        $output = array();
        $cdir = scandir($file_dir);
        foreach ($cdir as $key => $value) {
            if (in_array($value, array(".", ".."))) {
                continue;
            }
            if (is_dir($file_dir . '/' . $value)) {
                if (!in_array($value, self::DIR_TO_AVOID)) {
                    $output[] = $file_dir . '/' . $value;
                }
            }
        }
        return $output;
    }

    /**
     * @return array
     */
    public function files($file_dir, $pattern)
    {
        $output = array();
        $glob_pattern = $file_dir . '/' . $pattern;
        $this->logger->debug(__METHOD__ . $glob_pattern);
        $glob_list = glob($glob_pattern);
        $cdir = scandir($file_dir);
        foreach ($cdir as $key => $value) {
            if (in_array($value, array(".", ".."))) {
                continue;
            }
            if (is_dir($file_dir . '/' . $value)) {
                if (!in_array($value, self::DIR_TO_AVOID)) {
                    $file_results = $this->files($file_dir . '/' . $value . '/', $pattern);
                    $output = array_merge($output, $file_results);
                }
            } elseif (in_array($file_dir . '/' . $value, $glob_list)) {
                $output[] = $file_dir . '/' . $value;
            }
        }

        return $output;
    }

    /**
     * Test every line of the $files for the regex. Return the file and number of occurrences.
     * @param string $pattern
     * @param array  $files
     * @return array
     */
    public function regex($pattern, array $files)
    {
        $output = array();
        foreach ($files as $file) {
            if (preg_match_all($pattern, file_get_contents($file), $matches)) {
                $output[$file] = count($matches[0]);
            }
        }

        return $output;
    }
}
