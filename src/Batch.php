<?php

namespace Pendenga\File;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Batch
 * @package Pendenga\File
 */
class Batch
{
    use LoggerAwareTrait;

    protected $option = [
        'max_batch_bytes' => 50000,
    ];

    /**
     * @var Scan
     */
    protected $scan;

    /**
     * Batch constructor.
     * @param Scan            $scan
     * @param LoggerInterface $logger
     */
    public function __construct(Scan $scan, LoggerInterface $logger)
    {
        $this->setLogger($logger ?? new NullLogger());
        $this->scan = $scan;
    }

    /**
     * @param $file_dir
     * @return array
     */
    public function evaluate($file_dir)
    {
        $directories = $this->scan->directories($file_dir);
        $dir_count = count($directories);
        $output = [];
        $batch_i = 1;
        $batch = [
            'dirs'  => [],
            'bytes' => 0,
            'name'  => $file_dir . '/batch_' . str_pad($batch_i++, 2, '0', STR_PAD_LEFT),
        ];
        foreach ($directories as $i => $dir) {
            $bytes = $this->bytes($dir);
            if ($batch['bytes'] + $bytes < $this->option['max_batch_bytes']) {
                // merge into batch
                $batch['dirs'][] = $dir;
                $batch['bytes'] += $bytes;
            } else {
                // new batch
                $output[] = $batch;
                $batch = [
                    'dirs'  => [$dir],
                    'bytes' => $bytes,
                    'name'  => $file_dir . '/batch_' . str_pad($batch_i++, 2, '0', STR_PAD_LEFT),
                ];
            }
            $this->logger->debug(sprintf('dir %d of %d: %s is %d bytes', $i, $dir_count, $dir, $bytes));
        }
        $output[] = $batch;

        return $output;
    }

    /**
     * @param array $evaluation
     * @return array
     */
    public function execute(array $evaluation)
    {
        $commands = [];
        foreach ($evaluation as $batch) {
            $this->logger->debug("Creating " . $batch['name']);

            // action
            mkdir($batch['name']);

            // move files
            $dir_count = count($batch['dirs']);
            foreach ($batch['dirs'] as $i => $file) {
                $parts = explode('/', $file);
                $move_dir = array_pop($parts);
                $this->logger->debug(sprintf('moving %d of %d: %s', $i, $dir_count, $move_dir));
                // $this->logger->debug(
                //     "undo shell: mv " .
                //     str_replace(' ', '\ ', "{$batch['name']}/{$move_dir}") . ' ' .
                //     str_replace(' ', '\ ', "{$file}")
                // );

                // action
                rename($file, $batch['name'] . '/' . $move_dir);
            }
        }

        return $commands;
    }

    /**
     * @param $dir_path
     * @return int
     */
    public function bytes($dir_path): int
    {
        $files = $this->scan->files($dir_path, '*');
        $bytes = 0;
        foreach ($files as $file) {
            $bytes += filesize($file);
        }

        return $bytes;
    }

    /**
     * @param array $option
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->option[$key] = $value;

        return $this;
    }
}
