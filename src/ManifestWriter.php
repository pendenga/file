<?php

namespace Pendenga\File;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class ManifestWriter
 * @package Pendenga\File
 */
class ManifestWriter
{
    use LoggerAwareTrait;

    protected $writer;
    protected $header_written = false;

    /**
     * ManifestWriter constructor.
     * @param                      $file_path
     * @param LoggerInterface|null $logger
     */
    public function __construct($file_path, LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
        try {
            $this->writer = WriterFactory::createFromType(Type::CSV);
            $this->writer->openToFile($file_path);
            $this->logger->debug('manifest opened: ' . $file_path);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param array $header
     * @return $this
     */
    public function addHeader(array $header)
    {
        try {
            $this->writer->addRow(WriterEntityFactory::createRowFromArray($header));
            $this->header_written = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $this;
    }

    /**
     * @param array $row
     * @return $this
     */
    public function addRow(array $row)
    {
        try {
            // try to write array keys as header row
            if (!$this->header_written) {
                $this->writer->addRow(WriterEntityFactory::createRowFromArray(array_keys($row)));
            }
            // write row
            $this->writer->addRow(WriterEntityFactory::createRowFromArray($row));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $this;
    }

    /**
     * @return void
     */
    public function finish()
    {
        $this->logger->debug('closing manifest');
        $this->writer->close();
    }
}
