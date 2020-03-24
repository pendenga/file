<?php

namespace Pendenga\File;

/**
 * Interface PropInterface
 * @package Pendenga\File
 */
interface PropInterface
{
    /**
     * @return int
     * @throws FileNotFoundException
     */
    public function lines(): int;

    /**
     * @return int
     * @throws FileNotFoundException
     */
    public function bytes(): int;

    /**
     * @return bool
     * @throws FileNotFoundException
     */
    public function exists(): bool;
}
