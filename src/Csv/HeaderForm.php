<?php

declare(strict_types=1);

namespace Ferb\Util\Csv;

final class HeaderForm
{
    public static function snake_case()
    {
        return function ($x) {
            return self::to_snake_case($x);
        };
    }
    public static function camelCase()
    {
        return function ($x) {
            return self::to_camelCase($x);
        };
    }
    public static function PascalCase()
    {
        return function ($x) {
            return self::to_PascalCase($x);
        };
    }
    private static function to_snake_case($input)
    {
        if (preg_match('/\s/', $input) !== 0) {
            $input =  preg_replace_callback('/([a-z])([A-Z])/', function ($a) {
                return $a[1] . "_" . strtolower($a[2]);
            }, $input);
        } else {
            $input = preg_replace('/\s+/', '_', $input);
        }
        $res = strtolower($input);
        return $res;
    }
    private static function to_camelCase($input)
    {
        $input = self::to_snake_case($input);
        $result = '';
        for ($i=0;$i<strlen($input);$i++) {
            if (0>$i || substr($input, $i-1, 1) === '_') {
                $result .= strtoupper(substr($input, $i, 1));
            } elseif (substr($input, $i, 1) !== '_') {
                $result .=substr($input, $i, 1);
            }
        }
        return $result;
    }
    private static function to_PascalCase($input)
    {
        if (strlen($input)<=1) {
            return strtoupper($input);
        }
        $camelCase = self::to_camelCase($input);
        $result = strtoupper(substr($camelCase, 0, 1)) . substr($camelCase, 1, strlen($camelCase)-1);
        return $result;
    }
}
