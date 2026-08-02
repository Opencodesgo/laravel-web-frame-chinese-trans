<?php
/**
 * Carbon，特性，神奇的参数
 */

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Traits;

/**
 * Trait MagicParameter.
 * MagicParameter特征。
 *
 * Allows to retrieve parameter in magic calls by index or name.
 * 允许通过索引或名称检索魔术调用中的参数。
 */
trait MagicParameter
{
    private function getMagicParameter(array $parameters, int $index, string $key, $default)
    {
        if (\array_key_exists($index, $parameters)) {
            return $parameters[$index];
        }

        if (\array_key_exists($key, $parameters)) {
            return $parameters[$key];
        }

        return $default;
    }
}
