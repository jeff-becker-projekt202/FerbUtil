<?php

declare(strict_types=1);

namespace Ferb\Util\Tests;

use Ferb\Util\Config\ConfigBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConfigPathTests extends TestCase
{
    public function testBuilderArray()
    {
        $config = $this->make_config();
        $value = $config->section('test')->value();
        $this->assertEquals(1, $value);
    }

    public function testRoundTrip()
    {
        $config = $this->make_config();
        $value = $config->as('array');
        $this->assertEquals($this->data(), $value);
    }
    public function testCanLookPastMultipleLevels()
    {
        $config = $this->make_config();
        $value = $config->foo->bar->value();
        $this->assertEquals(2, $value);
    }
    private function make_config()
    {
        return (new ConfigBuilder())
        ->add_array($this->data())->create();
    }
    private function data()
    {
        return
        [
            'test' => 1,
            'foo' => [
                'bar' => 2,
                'baz' => 3,
            ],
        ];
    }
}
