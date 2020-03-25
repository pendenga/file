<?php

namespace Pendenga\File;

use http\Exception\BadQueryStringException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Rych\Random\Random;

/**
 * Class Generate
 * @package Pendenga\File
 */
class Generate
{
    use LoggerAwareTrait;

    /**
     * @var ManifestWriter
     */
    protected $manifest;

    protected $option = [
        'file_base_dir'  => '/tmp',
        'filename_chars' => 'abcdefghijklmnopqrstuvwxyz',
        'filename_len'   => 8,
        'filename_ext'   => '.txt',
        'min_dirs'       => 1,
        'max_dirs'       => 1,
        'min_files'      => 20,
        'max_files'      => 25,
        'min_lines'      => 100,
        'max_lines'      => 200,
        'min_line'       => 0,
        'max_line'       => 80,
    ];

    /**
     * Generate constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @return void
     */
    public function files()
    {
        $random = new Random();
        $base_dir = $this->option['file_base_dir'];
        $dir_count = $random->getRandomInteger($this->option['min_dirs'], $this->option['max_dirs']);
        foreach (range(1, $dir_count) as $h) {
            if ($dir_count == 1) {
                $this->logger->debug("only one directory");
                $dir = '';
            } else {
                $dir = '/' . $random->getRandomInteger(10000000, 99999999);
                mkdir($base_dir . $dir);
                $this->logger->debug("Writing to directory {$h}: " . $dir);
            }

            $file_count = $random->getRandomInteger($this->option['min_files'], $this->option['max_files']);
            foreach (range(1, $file_count) as $i) {
                // generate file name
                $file_name = $random->getRandomString(
                        $this->option['filename_len'],
                        $this->option['filename_chars']
                    ) . $this->option['filename_ext'];

                $file_path = $base_dir . $dir . '/' . $file_name;
                $this->logger->debug("Writing to file {$i} of {$file_count}: " . $file_path);

                // generate rows
                $file_rows = '';
                $line_count = $random->getRandomInteger($this->option['min_lines'], $this->option['max_lines']);
                foreach (range(1, $line_count) as $j) {
                    $line_length = $random->getRandomInteger($this->option['min_line'], $this->option['max_line']);
                    $file_rows .= $random->getRandomString($line_length) . "\n";
                }

                // write file
                $bytes = file_put_contents($file_path, $file_rows);

                // log to manifest
                if ($this->manifest) {
                    $this->manifest->addRow([$file_name, $line_count, $bytes, md5_file($file_path)]);
                }
            }
        }
        if ($this->manifest) {
            $this->manifest->finish();
        }
    }

    /**
     * @param ManifestWriter $manifest
     * @return $this
     */
    public function setManifest(ManifestWriter $manifest)
    {
        $this->manifest = $manifest;
        $this->manifest->setLogger($this->logger);
        try {
            $this->manifest->addHeader(['name', 'lines', 'bytes', 'checksum']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $this;
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
