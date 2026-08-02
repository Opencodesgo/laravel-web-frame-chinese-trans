<?php
/**
 * Symfony，Component，HttpFoundation，Session会话，属性，属性包接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Attribute;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * Attributes store.
 * 存储属性
 *
 * @author Drak <drak@zikula.org>
 */
interface AttributeBagInterface extends SessionBagInterface
{
    /**
     * Checks if an attribute is defined.
	 * 检查是否定义了属性
     *
     * @return bool
     */
    public function has(string $name);

    /**
     * Returns an attribute.
	 * 返回一个属性
     *
     * @param mixed $default The default value if not found
     *
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * Sets an attribute.
	 * 设置属性
     *
     * @param mixed $value
     */
    public function set(string $name, $value);

    /**
     * Returns attributes.
	 * 返回属性
     *
     * @return array<string, mixed>
     */
    public function all();

    public function replace(array $attributes);

    /**
     * Removes an attribute.
	 * 移除属性
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name);
}
