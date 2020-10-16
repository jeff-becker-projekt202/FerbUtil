<?php

declare(strict_types=1);

namespace Ferb\Util;

class Guard
{
    public static function using($disposables, $callable)
    {
        if (!is_array($disposables)) {
            $disposables = [$disposables];
        }

        try {
            return call_user_func_array($callable, $disposables);
        } finally {
            foreach ($disposables as $d) {
                if (is_object($d) && method_exists($d, 'dispose')) {
                    $d->dispose();
                }
            }
        }
    }
}
