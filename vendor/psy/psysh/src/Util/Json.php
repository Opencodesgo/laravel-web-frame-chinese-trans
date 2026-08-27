<?php
/**
 * Psy，工具，Json
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Util;

/**
 * A static class to wrap JSON encoding/decoding with PsySH's default options.
 * 一个静态类，用PsySH的默认选项包装JSON编码/解码。
 */
class Json
{
    /**
     * Encode a value as JSON.
     *
     * @param mixed $val
     * @param int   $opt
     */
    public static function encode($val, int $opt = 0): string
    {
        $opt |= \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;

        return \json_encode($val, $opt);
    }
}
