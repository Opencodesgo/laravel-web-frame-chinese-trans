<?php
/**
 * Symfony，Contracts，String，Slugger，Slugger 接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\String\Slugger;

use Symfony\Component\String\AbstractUnicodeString;

/**
 * Creates a URL-friendly slug from a given string.
 * 从给定字符串创建一个url友好的段塞。
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface SluggerInterface
{
    /**
     * Creates a slug for the given string and locale, using appropriate transliteration when needed.
	 * 为给定的字符串和区域设置创建一个段符，在需要时使用适当的音译。
     */
    public function slug(string $string, string $separator = '-', ?string $locale = null): AbstractUnicodeString;
}
