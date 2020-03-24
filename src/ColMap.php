<?php

namespace Pendenga\File;

use phpDocumentor\Reflection\File;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class ColMap
 * @package Pendenga\File
 */
class ColMap
{
    use LoggerAwareTrait;

    const NAME = 'col_name';
    const SIZE = 'col_size';
    const ROWS = 'col_rows';

    protected $col_index = [];
    protected $col_keys = [
        self::NAME,
        self::SIZE,
        self::ROWS,
    ];
    /**
     * @var string
     */
    protected $header_checksum;

    /**
     * TODO: provide a way to override this to match up custom column names
     * @var array
     */
    protected $col_match = [
        'file'     => self::NAME,
        'filename' => self::NAME,
        'size'     => self::SIZE,
        'byte'     => self::SIZE,
        'bytes'    => self::SIZE,
        'count'    => self::ROWS,
        'rows'     => self::ROWS,
    ];

    /**
     * ColMap constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @return array
     */
    public function columnKeys() {
        return array_keys($this->col_index);
    }

    /**
     * @param string $column must match with a defined column key
     * @return int
     * @throws FileException
     */
    public function index($column): int
    {
        if (!in_array($column, $this->col_keys)) {
            throw new FileException('invalid column: ' . $column);
        }
        if (!is_int($this->col_index[$column])) {
            throw new FileException('column not found: ' . $column);
        }

        return $this->col_index[$column];
    }

    /**
     * Test if this is the header row
     * @param array $header
     * @return bool
     */
    public function isHeader(array $header): bool
    {
        return ($this->header_checksum === md5(json_encode($header)));
    }

    /**
     * @param array $header
     * @throws FileException
     */
    public function parseHeader(array $header)
    {
        $this->header_checksum = md5(json_encode($header));
        foreach ($header as $i => $row_value) {
            $this->logger->debug('parsing ' . $row_value);
            if (isset($this->col_match[$row_value])) {
                $this->col_index[$this->col_match[$row_value]] = $i;
                $this->col_values[$i] = $row_value;
            }
        }
        if (!isset($this->col_index[self::NAME])) {
            throw new FileException('column name not idenfied in header row');
        }
        $this->logger->debug('parsed', $this->col_index);
        $this->logger->debug('values', $this->col_values);
    }

    /**
     * Get the value of the given column from the row
     * @param string $column
     * @param array $row
     * @return mixed
     * @throws FileException
     */
    public function value($column, array $row) {
        return $row[$this->index($column)];
    }
}
