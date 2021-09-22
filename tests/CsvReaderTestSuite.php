<?php

declare(strict_types=1);

namespace Ferb\Util\Tests;

use Ferb\Util\Csv\CsvFileReader;
use Ferb\Util\Csv\HeaderForm;
use Ferb\Util\Csv\RowMap;
use Ferb\Util\Guard;

/**
 * @internal
 * @covers \Ferb\Util\Csv\CsvFileReader
 */
class CsvReaderTestSuite extends \PHPUnit\Framework\TestCase
{
    public const FILENAME = "testtemp.csv";
    public static function setUpBeforeClass(): void
    {
        if (is_writable(self::FILENAME)) {
            unlink(self::FILENAME);
        }
        file_put_contents(self::FILENAME, "A,B,C\n1,2,3\n4,5,6");
    }
    public function testCanReadRows()
    {
        $csv = Guard::using(new CsvFileReader(self::FILENAME), function ($x) {
            return $x->to_array();
        });
        $this->assertSequenceEquals(['A','B','C'], $csv[0]);
        $this->assertSequenceEquals(['1','2','3'], $csv[1]);
        $this->assertSequenceEquals(['4','5','6'], $csv[2]);
    }
    public function testCanUseHeaders()
    {
        $csv =  Guard::using(new CsvFileReader(self::FILENAME, RowMap::use_headers()), function ($x) {
            return $x->to_array();
        });
        $this->assertEquals(['A'=>'1','B'=>'2','C'=>'3'], $csv[0]);
        $this->assertEquals(['A'=>'4','B'=>'5','C'=>'6'], $csv[1]);
    }
    public function testCanUseSnakeHeaders()
    {
        $csv =  Guard::using(new CsvFileReader(self::FILENAME, RowMap::use_headers(HeaderForm::snake_case())), function ($x) {
            return $x->to_array();
        });
        $this->assertEquals(['a'=>'1','b'=>'2','c'=>'3'], $csv[0]);
        $this->assertEquals(['a'=>'4','b'=>'5','c'=>'6'], $csv[1]);
    }
    public function testCanUseSnakeHeadersFromString()
    {
        $csv =  Guard::using(new CsvFileReader(self::FILENAME, RowMap::use_headers('snake_case')), function ($x) {
            return $x->to_array();
        });
        $this->assertEquals(['a'=>'1','b'=>'2','c'=>'3'], $csv[0]);
        $this->assertEquals(['a'=>'4','b'=>'5','c'=>'6'], $csv[1]);
    }

    public function testSnakeCaseTransformerIncludesUnderscores(){

        $t = HeaderForm::snake_case();
        $this->assertEquals('test_header', $t('Test Header'));
    }
    public function testCanUseNames()
    {
        $csv =  Guard::using(new CsvFileReader(self::FILENAME, RowMap::use_names(['first','second','third'])), function ($x) {
            return $x->to_array();
        });
        $this->assertEquals(['first'=>'A','second'=>'B','third'=>'C'], $csv[0]);
        $this->assertEquals(['first'=>'1','second'=>'2','third'=>'3'], $csv[1]);
        $this->assertEquals(['first'=>'4','second'=>'5','third'=>'6'], $csv[2]);
    }

    public function testCanUseHeadersSnakeSpec()
    {
        $csv =  Guard::using(new CsvFileReader(self::FILENAME, ['use_headers'=>'snake_case']), function ($x) {
            return $x->to_array();
        });
        $this->assertEquals(['a'=>'1','b'=>'2','c'=>'3'], $csv[0]);
        $this->assertEquals(['a'=>'4','b'=>'5','c'=>'6'], $csv[1]);
    }
    public function testCanUseNamesSpec()
    {
        $csv =  Guard::using(new CsvFileReader(self::FILENAME, ['use_names'=>['first','second','third']]), function ($x) {
            return $x->to_array();
        });
        $this->assertEquals(['first'=>'A','second'=>'B','third'=>'C'], $csv[0]);
        $this->assertEquals(['first'=>'1','second'=>'2','third'=>'3'], $csv[1]);
        $this->assertEquals(['first'=>'4','second'=>'5','third'=>'6'], $csv[2]);
    }

    public static function tearDownAfterClass(): void
    {
        if (is_writable(self::FILENAME)) {
            unlink(self::FILENAME);
        }
    }


    public function assertSequenceEquals($expected, $actual, $message = '')
    {
        $expected = is_array($expected) ? $expected : \iterator_to_array($expected);
        $actual = is_array($actual) ? $actual : \iterator_to_array($actual);

        return $this->assertEquals($expected, $actual, $message);
    }
}
