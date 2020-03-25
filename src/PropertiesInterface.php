<?php

namespace Pendenga\File;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface PropertiesInterface
 * @package Pendenga\File
 */
interface PropertiesInterface extends LoggerAwareInterface
{
    /**
     * Number of bytes in the file
     * @return int
     * @throws FileNotFoundException
     */
    public function bytes(): int;

    /**
     * Calculates the md5 checksum for the file
     * @return string
     * @throws FileNotFoundException
     */
    public function checksum(): string;

    /**
     * Does the file exist (true or FileNotFoundException)
     * @return bool
     * @throws FileNotFoundException
     */
    public function exists(): bool;

    /**
     * Return the full path of the file
     * @return string
     */
    public function fullPath(): string;

    /**
     * Number of lines in the file
     * @return int
     * @throws FileNotFoundException
     */
    public function lines(): int;
}
