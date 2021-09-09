<?php

declare(strict_types=1);

namespace Ferb\Util\Csv;

use Ferb\Util\FluentIterator;
use InvalidArgumentException;

class CsvFileReader extends FluentIterator
{
    private $iterator;
    public function __construct($fileName, $transformer = null)
    {
        $this->iterator = self::create($fileName, RowMap::build($transformer));
        parent::__construct($this->iterator);
    }

    public function as_objects()
    {
        return $this->map(function ($x) {
            return (object)$x;
        });
    }
    public function headers(){ return $this->iterator->firstRow; }
    private static function create($fileName, RowMap $transformer)
    {
        return new class($fileName, $transformer) implements \Iterator {
            private $fileName;
            private $transformer;

            private $rowIndex;
            private $fileHandle;
            public $firstRow;
            private $rows = [];

            public function __construct($fileName, RowMap $transformer)
            {
                $this->fileName = $fileName;
                $this->transformer = $transformer;
            }

            public function rewind()
            {
                if (null != $this->fileHandle) {
                    fclose($this->fileHandle);
                }
                $this->rowIndex = -1;
                $this->rows = [];
                $this->fileHandle = fopen($this->fileName, 'r');
                $this->firstRow = null;
                if (false == $this->fileHandle) {
                    throw new InvalidArgumentException("The fileName supplied does not point to a valid file {$this->fileName}");
                }

                $this->next();
            }

            public function current()
            {
                return $this->rows[0];
            }

            public function key()
            {
                return $this->rowIndex;
            }

            public function next()
            {
                if (null != $this->fileHandle) {
                    $row = fgetcsv($this->fileHandle);
                    ++$this->rowIndex;
                    if ($row) {
                        if (0 == $this->rowIndex) {
                            $this->firstRow = $this->transformer->clean_headers($row);
                            if ($this->transformer->skip_first_row()) {
                                $row2 = fgetcsv($this->fileHandle);
                                if (null != $row2) {
                                    array_push($this->rows, $this->transformer->transform($this->rowIndex, $row2, $this->firstRow));
                                } else {
                                    $this->dispose();
                                }
                            } else {
                                array_push($this->rows, $this->transformer->transform($this->rowIndex, $row, $this->firstRow));
                            }
                            return;
                        } else {
                            array_push($this->rows, $this->transformer->transform($this->rowIndex, $row, $this->firstRow));
                        }
                    } else {
                        $this->dispose();
                    }
                    if (count($this->rows) > 0) {
                        array_shift($this->rows);
                    }
                }
            }

            public function valid()
            {
                if (count($this->rows) > 0) {
                    return true;
                }

                if (null != $this->fileHandle) {
                    $this->dispose();
                }

                return false;
            }

            public function dispose()
            {
                if (null != $this->fileHandle) {
                    fclose($this->fileHandle);
                    $this->rows = [];
                    $this->rowIndex = -1;
                    $this->fileHandle = null;
                    $this->firstRow = null;
                }
            }
        };
    }
}
