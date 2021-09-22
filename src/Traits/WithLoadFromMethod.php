<?php

declare(strict_types=1);

namespace Ferb\Util\Traits;

trait WithLoadFromMethod
{
    public function load_from($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        } elseif (is_object($data)) {
            foreach (get_object_vars($data) as $key=>$value) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }
}
