<?php

declare(strict_types=1);

namespace Ferb\Util\Csv;

use Ferb\Util\FluentIterator;
use InvalidArgumentException;

class CsvFileReader extends FluentIterator
{
    private $iterator;
    public function __construct($fileOrName, $transformer = null)
    {
        $this->iterator = self::create($fileOrName, RowMap::build($transformer));
        parent::__construct($this->iterator);
    }
//https://stackoverflow.com/questions/2276626/is-there-a-way-to-access-a-string-as-a-filehandle-in-php
    public function as_objects()
    {
        return $this->map(function ($x) {
            return (object)$x;
        });
    }
    public function headers()
    {
        return $this->iterator->firstRow;
    }
    private static function create($fileName, RowMap $transformer)
    {
        if($fileName instanceof \SplFileInfo){
            
        }
        return new class($fileName, $transformer) implements \Iterator {
            private $fileName;
            private $transformer;

            private $rowIndex;
            private $file;
            public $firstRow;
            private $rows = [];

            public function __construct($fileName, RowMap $transformer)
            {
                $this->fileName = $fileName;
                $this->transformer = $transformer;
            }

            public function rewind()
            {
                if (null === $this->file) {
                    
                    if($this->fileName instanceof \SplFileObject){
                        $this->file = $this->fileName;
                    }
                    else{
                        $this->file = new \SplFileObject($this->fileName);
                    }
                }
                $this->file->rewind();
                $this->rowIndex = -1;
                $this->rows = [];
                
                $this->firstRow = null;
   

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
                if (null != $this->file) {
                    $row = $this->file->fgetcsv();
                    ++$this->rowIndex;
                    if ($row) {
                        if (0 == $this->rowIndex) {
                            $this->firstRow = $this->transformer->clean_headers($row);
                            if ($this->transformer->skip_first_row()) {
                                $row2 = $this->file->fgetcsv();
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

                if (null != $this->file) {
                    $this->dispose();
                }

                return false;
            }

            public function dispose()
            {
                if (null != $this->file) {
                    $this->rows = [];
                    $this->rowIndex = -1;
                    $this->file = null;
                }
            }
        };
    }
}
