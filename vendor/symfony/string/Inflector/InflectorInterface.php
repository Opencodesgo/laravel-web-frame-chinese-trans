<?php
/**
 * Symfony，Contracts，String，偏转器，偏转器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\String\Inflector;

interface InflectorInterface
{
    /**
     * Returns the singular forms of a string.
	 * 返回字符串的单数形式。
     *
     * If the method can't determine the form with certainty, several possible singulars are returned.
	 * 如果该方法不能确定形式，则返回几个可能的单数。
     *
     * @return string[]
     */
    public function singularize(string $plural): array;

    /**
     * Returns the plural forms of a string.
	 * 返回字符串的复数形式。
     *
     * If the method can't determine the form with certainty, several possible plurals are returned.
	 * 如果该方法不能确定形式，则返回几个可能的复数形式。
     *
     * @return string[]
     */
    public function pluralize(string $singular): array;
}
