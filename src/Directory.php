<?php

namespace Pendenga\File;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Directory
 * @package Pendenga\File
 */
class Directory {
    use LoggerAwareTrait;

    /**
     * @var Scan
     */
    protected $scan;

    /**
     * Directory constructor.
     * @param Scan                 $scan
     * @param LoggerInterface|null $logger
     */
    public function __construct(Scan $scan, LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
        $this->scan = $scan;
        $this->scan->setLogger($this->logger);
    }

    /**
     * @param $dir_path
     * @return void
     * @throws FileException
     */
    public function checkEmpty($dir_path) {
        $files = $this->files($dir_path);
        $this->logger->debug(__METHOD__, $files);
        $this->logger->debug('count files ' . count($files));
        if (count($files) > 0) {
            $this->logger->debug('throwing exception: Directory not empty: ' . $dir_path);
            throw new FileException('Directory not empty: ' . $dir_path);
        }
    }

    /**
     * @param $dir_path
     * @return array
     */
    public function files($dir_path) {
        return $this->scan->files($dir_path, '*');
    }

    /**
     * @param $dir_path
     * @return int
     */
    public function deleteFiles($dir_path) {
        $files = $this->scan->files($dir_path, '*');
        $file_count = count($files);
        foreach ($files as $file) {
            $this->logger->debug('deleting ' . $file);
            unlink($file);
        }
        return $file_count;
    }

    /**
     * @param $dir_path
     * @return int
     */
    public function deleteSubDirs($dir_path) {
        $dirs = $this->scan->directories($dir_path);
        $dir_count = count($dirs);
        foreach ($dirs as $dir) {
            $this->logger->debug('deleting ' . $dir);
            rmdir($dir);
        }
        return $dir_count;
    }
}
