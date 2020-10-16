<?php

declare(strict_types=1);

namespace Ferb\Util\Config;

use Ferb\Util\Config\ConfigPath;
use Ferb\Util\FluentIterator;

class ConfigSection
{
    private $root;
    private $path;
    private $key;

    private $children;

    public function __construct(ConfigRoot $root, string $path)
    {
        $this->root = $root;
        $this->path = $path;
        $this->key = ConfigPath::get_section_key($path);
        if (empty($this->key) && !empty($path)) {
            $this->key = $path;
        }
    }

    public function value(string $key = null)
    {
        return $this->root->value(ConfigPath::combine([$this->path, $key ]));
    }

    public function section(string $key): ?ConfigSection
    {
        return $this->root->section(ConfigPath::combine([$this->path, $key]));
    }
    public function __get(string $name)
    {
        return $this->section($name);
    }

    public function children()
    {
        if (!isset($this->children)) {
            $this->children = $this->root->children($this->path)->to_array();
        }

        return FluentIterator::from($this->children);
    }

    public function key()
    {
        return $this->key;
    }

    public function path()
    {
        return $this->path;
    }

    public function has_children()
    {
        return $this->children()->some(function ($x) {
            return true;
        });
    }
    public function __toString()
    {
        return $this->as('string');
    }

    public function as($type)
    {
        if ('array' === $type) {
            return $this->as_array();
        }
        if ('object' === $type) {
            return (object) ($this->as_array());
        }
        $value = $this->value();
        if ('string' === $type) {
            return $value;
        }
        if ('int' === $type) {
            return intval($value);
        }
        if ('float' === $type) {
            return floatval($value);
        }
        if ('bool' === $type) {
            $v = \strtolower($value);
            return 'true' === $v || 'yes' === $v || '1' === $v || 'y' === $v;
        }
        if ('callable' === $type) {
            return $value;
        }
        if (\class_exists($type)) {
            return new $type($this);
        }

        return null;
    }

    private static function make_array($children, $root, $path)
    {
        $result = [];
        foreach ($children as $child) {
            $child_path = ConfigPath::combine([$path, $child]);
            if ($root->has_children($child_path)) {
                $result[$child] = self::make_array($root->children($child_path), $root, $child_path);
            } else {
                $result[$child] = $root->value($child_path);
            }
        }
        return $result;
    }
    private function as_array()
    {
        return   self::make_array($this->children(), $this->root, $this->path);
    }
}
