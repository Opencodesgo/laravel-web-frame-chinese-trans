<?php
/**
 * Psy，反射，反射命名空间
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Reflection;

/**
 * A fake Reflector for namespaces.
 * 名称空间的伪反射器。
 */
class ReflectionNamespace implements \Reflector
{
    private string $name;

    /**
     * Construct a ReflectionNamespace object.
	 * 构造一个ReflectionNamespace对象
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Gets the constant name.
	 * 获取常量名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * This can't (and shouldn't) do anything :).
     *
     * @throws \RuntimeException
     */
    public static function export($name)
    {
        throw new \RuntimeException('Not yet implemented because it\'s unclear what I should do here :)');
    }

    /**
     * To string.
	 * 转换为字符串
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
