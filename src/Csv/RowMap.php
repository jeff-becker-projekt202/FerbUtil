<?php

declare(strict_types=1);

namespace Ferb\Util\Csv;

use InvalidArgumentException;

abstract class RowMap
{
    abstract public function clean_headers($header_data);
    abstract public function transform($row_index, $row_data, $header_data);
    abstract public function skip_first_row();

    public static function build($spec)
    {
        if ($spec === null) {
            return RowMap::default();
        }
        if (is_a($spec, RowMap::class)) {
            return $spec;
        }
        if (is_string($spec) && (strpos($spec, 'use_headers') === 0 || strpos($spec, 'use_names') === 0)) {
            $parts = explode(':', $spec);
            $res = [];

            $res[$parts[0]] = count($parts) > 1 ? $parts[1] : true;
            if (isset($res['use_names'])) {
                $res['use_names'] = explode(',', $res['use_names']);
            }
            return self::build($parts);
        }
        if (is_array($spec)) {
            if (isset($spec['use_headers'])) {
                return RowMap::use_headers($spec['use_headers']);
            }
            if (isset($spec['use_names'])) {
                return RowMap::use_names($spec['use_names']);
            }
        }
        throw new InvalidArgumentException("The transformer must be either a RowMap or array specification");
    }
    public static function default()
    {
        return new class() extends RowMap {
            public function skip_first_row()
            {
                return false;
            }
            public function transform($row_index, $row_data, $header_data)
            {
                return $row_data;
            }
            public function clean_headers($header_data)
            {
                return $header_data;
            }
        };
    }
    public static function use_headers($cleaner = null)
    {
        if ($cleaner === true) {
            $cleaner = null;
        }
        if (isset($cleaner) && is_string($cleaner)) {
            $method = strtolower($cleaner);
            if (method_exists(HeaderForm::class, $method)) {
                $name = $method;
                $cleaner = (function () use ($name) {
                    return call_user_func([HeaderForm::class, $name]);
                })();
            } else {
                throw new InvalidArgumentException("$cleaner is not a valid header transform name");
            }
        }
        return new class($cleaner) extends RowMap {
            private $cleaner;
            public function __construct(callable $cleaner = null)
            {
                $this->cleaner = $cleaner ?: function ($x) {
                    return $x;
                };
            }
            public function skip_first_row()
            {
                return true;
            }
            public function clean_headers($header_data)
            {
                $result = [];
                foreach ($header_data as $key=>$value) {
                    $result[$key] = ($this->cleaner)($value);
                }

                return $result;
            }
            public function transform($row_index, $row_data, $header_data)
            {
                $result = [];
                foreach ($header_data as $key=>$value) {
                    $result[$value] = (isset($row_data[$key]) ? $row_data[$key] : null);
                }
                return $result;
            }
        };
    }
    public static function use_names(array $property_names, $skip_first_row = false)
    {
        return new class($property_names, $skip_first_row) extends RowMap {
            private $property_names;
            private $skip_first_row;
            public function __construct(array $property_names, $skip_first_row)
            {
                $this->property_names = $property_names;
                $this->skip_first_row =$skip_first_row;
            }
            public function skip_first_row()
            {
                return $this->skip_first_row;
            }
            public function clean_headers($header_data)
            {
                return $this->property_names;
            }
            public function transform($row_index, $row_data, $header_data)
            {
                $result = [];
                foreach ($header_data as $key=>$value) {
                    $result[$value] = (isset($row_data[$key]) ? $row_data[$key] : null);
                }
                return $result;
            }
        };
    }
}
