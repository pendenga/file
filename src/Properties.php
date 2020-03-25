<?php

namespace Pendenga\File;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Properties
 * @package Pendenga\File
 */
class Properties implements PropertiesInterface
{
    use LoggerAwareTrait;

    protected $full_file_name;

    /**
     * Properties constructor.
     * @param string               $full_file_name
     * @param LoggerInterface|null $logger
     */
    public function __construct($full_file_name, LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
        $this->full_file_name = $full_file_name;
    }

    /**
     * @inheritDoc
     */
    public function bytes(): int
    {
        $this->exists();

        return filesize($this->fullPath());
    }

    /**
     * @inheritDoc
     */
    public function checksum(): string {
        $this->exists();
        return md5_file($this->fullPath());
    }

    /**
     * @inheritDoc
     */
    public function exists(): bool
    {
        if (!file_exists($this->fullPath())) {
            throw new FileNotFoundException('File not found: ' . $this->fullPath());
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function fullPath(): string
    {
        return $this->full_file_name;
    }

    /**
     * @inheritDoc
     */
    public function lines(): int
    {
        $this->exists();

        $line_count = 0;
        $handle = fopen($this->fullPath(), "r");
        while (!feof($handle)) {
            $ignore = fgets($handle);
            if ($ignore !== false) {
                $line_count++;
            }
        }
        fclose($handle);

        return $line_count;
    }
}
