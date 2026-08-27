<?php
/**
 * Symfony，Component，HttpFoundation，会话，属性，属性包接口
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
     */
    public function has(string $name): bool;

    /**
     * Returns an attribute.
	 * 返回一个属性
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Sets an attribute.
	 * 设置属性
     *
     * @return void
     */
    public function set(string $name, mixed $value);

    /**
     * Returns attributes.
	 * 返回属性
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * @return void
     */
    public function replace(array $attributes);

    /**
     * Removes an attribute.
	 * 移除属性
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name): mixed;
}
