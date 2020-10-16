<?php

declare(strict_types=1);

namespace Ferb\Util\Config;

class ConfigBuilder
{
    private $providers = [];
    private $do_add = true;

    public function add($provider)
    {
        if ($this->do_add) {
            $this->providers[] = $provider;
        }
        $this->do_add = true;

        return $this;
    }

    public function create()
    {
        return new ConfigRoot($this->providers);
    }

    public function add_json($file)
    {
        return $this->add(ConfigProvider::from_json($file));
    }

    public function add_env_vars()
    {
        return $this->add(ConfigProvider::from_environment());
    }

    public function add_include_file($file)
    {
        return $this->add(ConfigProvider::from_include($file));
    }

    public function add_array($array)
    {
        return $this->add(ConfigProvider::from_array($array));
    }
}
