<?php

declare(strict_types=1);

namespace Ferb\Util\Tests;

use Ferb\Util\Config\ConfigBuilder;
use Ferb\Util\Traits\WithLoadFromMethod;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TraitTests extends TestCase
{
    public function testCanLoadWithArray()
    {
        $foo = (new class() {
            use WithLoadFromMethod;
        })->load_from(['a'=>1,'b'=>2]);
        $this->assertEquals(1, $foo->a);
        $this->assertEquals(2, $foo->b);
    }
    public function testCanLoadObject()
    {
        $foo = (new class() {
            use WithLoadFromMethod;
        })->load_from(new class() {
            public $a = 1;
            public $b = 2;
        });
        $this->assertEquals(1, $foo->a);
        $this->assertEquals(2, $foo->b);
    }
}
