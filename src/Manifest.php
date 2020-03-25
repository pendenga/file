<?php

namespace Pendenga\File;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\CSV\RowIterator;
use Box\Spout\Reader\CSV\Sheet;
use Pendenga\Log\EchoLogger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function GuzzleHttp\Psr7\_parse_message;

/**
 * Class Manifest
 * @package Pendenga\File
 */
class Manifest implements ManifestInterface
{
    use LoggerAwareTrait;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var ColumnMap
     */
    protected $map;

    protected $manifest_file_name;

    /**
     * Manifest constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(ColumnMap $map, LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
        $this->map = $map;
        $this->map->setLogger($this->logger);
    }

    /**
     * make $file_name relative and $full_file_path full
     * @param string $file_dir just the directory
     * @param string $file_name
     * @return array
     */
    public function paths($file_dir, $file_name)
    {
        if (stripos($file_name, $file_dir) === 0) {
            $full_file_path = $file_name;
            $file_name = implode('/', array_diff(explode('/', $file_name), explode('/', $file_dir)));
        } else {
            $full_file_path = $file_dir . '/' . $file_name;
        }
        $this->logger->debug(__METHOD__ . ' ' . $file_name . ' or ' . $full_file_path);

        return [$file_name, $full_file_path];
    }

    /**
     * @inheritDoc
     */
    public function load($manifest_file_name)
    {
        try {
            $this->reader = ReaderFactory::createFromType(Type::CSV);
            $this->reader->open($manifest_file_name);
            $this->manifest_file_name = $manifest_file_name;

            // map header row
            /* @var Sheet $sheet */
            $sheet = $this->reader->getSheetIterator()->current();
            /* @var Row $row */
            $rs = [];
            foreach ($sheet->getRowIterator() as $row) {
                if ($rs = $row->toArray()) {
                    $this->map->parseHeader($rs);
                    break;
                }
            }
        } catch (\Exception $e) {
            throw new FileException($e->getMessage());
        }

        return $this;
    }

    /**
     * @param array $validation
     * @return array
     */
    protected function processResults(array $validation)
    {
        $output = [];
        $this->logger->debug(__METHOD__, $validation);
        foreach ($validation as $filename => $rs) {
            $this->logger->debug(__METHOD__ . ' for ' . $filename, $rs);
            if (!$rs['exists'] && $rs['in_manifest']) {
                $output['Manifest item missing from directory'][] = $filename;
            }
            if ($rs['exists'] && !$rs['in_manifest']) {
                if ($filename == $this->manifest_file_name) {
                    continue;
                }
                $output['File missing from manifest'][] = $filename;
            }
            if ($rs['exception']) {
                $output[$rs['exception']][] = $filename;
            }
            foreach ($rs as $key => $val) {
                if ($val === false) {
                    $output['Failed check: ' . $key][] = $filename;
                }
            }
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function validateFile($file_dir, $file_name)
    {
        list($file_name, $full_file_path) = $this->paths($file_dir, $file_name);
        $output = [];

        // test just exists on this file
        $prop = new Properties($full_file_path, $this->logger);
        try {
            $prop->exists();
            $output[$full_file_path]['exists'] = true;
        } catch (FileNotFoundException $e) {
            $output[$full_file_path]['exception'] = $e->getMessage();
        }

        try {
            /* @var Sheet $sheet */
            foreach ($this->reader->getSheetIterator() as $sheet) {
                /* @var Row $row */
                foreach ($sheet->getRowIterator() as $row) {
                    $rs = $row->toArray();
                    if ($this->map->isHeader($rs)) {
                        continue;
                    }

                    try {
                        $file_from_manifest = $this->map->value(ColumnMap::NAME, $rs);
                        $this->logger->debug(__METHOD__ . " $file_name == $file_from_manifest");
                        if ($file_name == $file_from_manifest) {
                            $output[$full_file_path]['in_manifest'] = true;
                            $this->validateRow($prop, $rs, $output);
                        }
                    } catch (FileException $e) {
                        $output[$full_file_path]['exception'] = $e->getMessage();
                    } catch (FileNotFoundException $e) {
                        $output[$full_file_path]['exception'] = $e->getMessage();
                    }
                }
            }
        } catch (\Exception $e) {
            throw new FileException($e->getMessage());
        }

        return $this->processResults($output);
    }

    /**
     * @inheritDoc
     */
    public function validateFiles($file_dir, ScanInterface $scan)
    {
        $this->logger->debug(__METHOD__ . ' ' . $file_dir);
        $output = [];

        // read all files from directory
        $scan->setLogger($this->logger);
        $files = $scan->files($file_dir, '*.*');
        foreach ($files as $file) {
            $output[$file]['exists'] = true;
        }

        // check manifest
        try {
            /* @var Sheet $sheet */
            foreach ($this->reader->getSheetIterator() as $sheet) {
                /* @var Row $row */
                foreach ($sheet->getRowIterator() as $row) {
                    $rs = $row->toArray();
                    if ($this->map->isHeader($rs)) {
                        continue;
                    }

                    try {
                        $file_from_manifest = $this->map->value(ColumnMap::NAME, $rs);
                        $full_file_path = $file_dir . '/' . $file_from_manifest;
                        $output[$full_file_path]['in_manifest'] = true;
                        $prop = new Properties($full_file_path, $this->logger);
                        $this->validateRow($prop, $rs, $output);
                    } catch (FileException $e) {
                        $output[$full_file_path]['exception'] = $e->getMessage();
                    } catch (FileNotFoundException $e) {
                        $output[$full_file_path]['exception'] = $e->getMessage();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return $this->processResults($output);
    }

    /**
     * @param Properties $prop
     * @param array      $row
     * @param array      $output
     * @return void
     * @throws FileException
     * @throws FileNotFoundException
     */
    protected function validateRow(Properties $prop, array $row, array &$output)
    {
        foreach ($this->map->columnKeys() as $key) {
            switch ($key) {
                case ColumnMap::CHECK:
                    $expected_check = $this->map->value(ColumnMap::CHECK, $row);
                    $actual_check = $prop->checksum();
                    $this->logger->debug("checking $expected_check != $actual_check");
                    if ($expected_check != $actual_check) {
                        throw new FileException('checksum failed');
                    }
                    $output[$prop->fullPath()]['checksum'] = true;
                    break;
                case ColumnMap::NAME:
                    $prop->exists();
                    $output[$prop->fullPath()]['exists'] = true;
                    break;
                case ColumnMap::SIZE:
                    $expected_size = $this->map->value(ColumnMap::SIZE, $row);
                    $actual_size = $prop->bytes();
                    $this->logger->debug("checking $expected_size != $actual_size");
                    if ($expected_size != $actual_size) {
                        throw new FileException('file size check failed');
                    }
                    $output[$prop->fullPath()]['size_check'] = true;
                    break;
                case ColumnMap::ROWS:
                    $expected_rows = $this->map->value(ColumnMap::ROWS, $row);
                    $actual_rows = $prop->lines();
                    $this->logger->debug("checking $expected_rows != $actual_rows");
                    if ($expected_rows != $actual_rows) {
                        throw new FileException('file rows check failed');
                    }
                    $output[$prop->fullPath()]['row_check'] = true;
                    break;
            }
        }
    }
}
