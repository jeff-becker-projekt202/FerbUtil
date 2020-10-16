<?php

namespace Ferb\Util;

class Lazy implements \ArrayAccess, \Iterator
{
    private $have_constructed = false;
    private $inner_value;
    private $factory;
    private $is_array = false;

    public function __construct($factory)
    {
        $this->factory = $factory;
        $this->have_constructed = false;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $this->ensure_value_created();

        return \call_user_func_array([$this->inner_value, $name], $arguments);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->ensure_value_created();
        $this->inner_value->{$name} = $value;
    }

    public function __get(string $name): mixed
    {
        $this->ensure_value_created();

        return $this->inner_value->{$name};
    }

    public function __isset(string $name): bool
    {
        $this->ensure_value_created();

        return isset($this->inner_value->{$name});
    }

    public function __unset(string $name): void
    {
        $this->ensure_value_created();
        unset($this->inner_value->{$name});
    }

    public function offsetSet($offset, $value)
    {
        $this->ensure_value_created();
        if (is_null($offset)) {
            $this->inner_value[] = $value;
        } else {
            $this->inner_value[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        $this->ensure_value_created();

        return isset($this->inner_value[$offset]);
    }

    public function offsetUnset($offset)
    {
        $this->ensure_value_created();
        unset($this->inner_value[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->ensure_value_created();

        return isset($this->inner_value[$offset]) ? $this->inner_value[$offset] : null;
    }

    public function current()
    {
        $this->ensure_value_created();
        if ($this->is_array) {
            return current($this->inner_value);
        }

        return $this->inner_value->current();
    }

    public function key()
    {
        $this->ensure_value_created();
        if ($this->is_array) {
            return key($this->inner_value);
        }

        return $this->inner_value->key();
    }

    public function next()
    {
        $this->ensure_value_created();
        if ($this->is_array) {
            return next($this->inner_value);
        }

        $this->inner_value->next();
    }

    public function rewind()
    {
        $this->ensure_value_created();
        if ($this->is_array) {
            return reset($this->inner_value);
        }

        $this->inner_value->rewind();
    }

    public function valid()
    {
        $this->ensure_value_created();
        if ($this->is_array) {
            return null !== key($this->inner_value);
        }

        return $this->inner_value->valid();
    }

    private function ensure_value_created()
    {
        if (!$this->have_constructed) {
            $this->inner_value = ($this->factory)();
            $this->is_array = is_array($this->inner_value);
            $this->have_constructed = true;
        }

        return $this->inner_value;
    }
}
