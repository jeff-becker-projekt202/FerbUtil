<?php

declare(strict_types=1);

namespace Ferb\Util\Config;

use Ferb\Util\FluentIterator;

class ConfigRoot
{
    private $providers;
    private $providers_reverse;

    private $path_children = [];

    public function __construct(array $providers)
    {
        $this->providers = array_values($providers ?? []);
        $this->providers_reverse = \array_reverse($this->providers);
    }

    public function value(string $key)
    {
        if (empty($key)) {
            return null;
        }
        foreach ($this->providers_reverse as $provider) {
            list($found, $value) = $provider->get($key);
            if ($found) {
                return $value;
            }
        }

        return null;
    }

    public function section(string $key = ''): ConfigSection
    {
        return new ConfigSection($this, $key);
    }
    public function __get(string $name)
    {
        return $this->section($name);
    }

    public function children($path = '')
    {
        if (!isset($this->path_children[$path])) {
            $this->path_children[$path] = FluentIterator::from($this->providers)
                ->reduce(function ($a, $p) use ($path) {
                    return $p->get_child_keys($a, $path);
                }, FluentIterator::none())->to_array();
        }

        return FluentIterator::from($this->path_children[$path]);
    }


    public function has_children($path = ''): bool
    {
        return $this->children($path)
            ->some(function ($p) {
                return true;
            })
        ;
    }

    public function as($type)
    {
        return (new ConfigSection($this, ''))->as($type);
    }
}
