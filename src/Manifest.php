<?php

namespace Pendenga\File;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\CSV\Sheet;
use mysql_xdevapi\Exception;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
     * @var ColMap
     */
    protected $map;

    /**
     * ColMap constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @inheritDoc
     */
    public function load($manifest_file_name)
    {
        try {
            $this->reader = ReaderFactory::createFromType(Type::CSV);
            $this->reader->open($manifest_file_name);

            // map header row
            $this->map = new ColMap();
            $this->map->parseHeader($this->reader->getSheetIterator()->getRowIterator()->toArray());
        } catch (\Exception $e) {
            throw new FileException($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function validateFile($file_dir, $file_name)
    {
        try {
            /* @var Sheet $sheet */
            foreach ($this->reader->getSheetIterator() as $sheet) {
                /* @var Row $row */
                foreach ($sheet->getRowIterator() as $row) {
                    $rs = $row->toArray();
                    if ($this->map->isHeader($rs)) {
                        continue;
                    }

                    $file_name = $this->map->value(ColMap::NAME, $rs);
                    foreach ($this->map->columnKeys() as $key) {
                        $prop = new Prop($file_name, $this->logger);
                        switch ($key) {
                            case ColMap::NAME:
                                $prop->exists();
                                break;
                            case ColMap::SIZE:
                                $expected_size = $this->map->value(ColMap::SIZE, $rs);
                                $actual_size = $prop->bytes();
                                if ($expected_size != $actual_size) {
                                    throw new Exception(
                                        sprintf(
                                            'file %s was %d bytes, not expected %d bytes',
                                            $file_name,
                                            $actual_size,
                                            $expected_size
                                        )
                                    );
                                }
                                break;
                            case ColMap::ROWS:
                                $expected_rows = $this->map->value(ColMap::ROWS, $rs);
                                $actual_rows = $prop->lines();
                                if ($expected_rows != $actual_rows) {
                                    throw new Exception(
                                        sprintf(
                                            'file %s was %d rows, not expected %d rows',
                                            $file_name,
                                            $actual_rows,
                                            $expected_rows
                                        )
                                    );
                                }
                                break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            throw new FileException($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function validateFiles($file_dir)
    {
        // TODO: Implement validateFiles() method.
    }
}
