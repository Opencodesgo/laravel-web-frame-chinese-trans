<?php
/**
 * Mockery，发生器，目标类接口
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Generator;

interface TargetClassInterface
{
    /**
     * Returns a new instance of the current TargetClassInterface's implementation.
	 * 返回当前TargetClassInterface实现的新实例
     *
     * @param class-string $name
     *
     * @return TargetClassInterface
     */
    public static function factory($name);

    /**
     * Returns the targetClass's attributes.
	 * 返回targetClass的属性
     *
     * @return array<class-string>
     */
    public function getAttributes();

    /**
     * Returns the targetClass's interfaces.
	 * 返回targetClass的接口
     *
     * @return array<TargetClassInterface>
     */
    public function getInterfaces();

    /**
     * Returns the targetClass's methods.
	 * 返回targetClass的方法
     *
     * @return array<Method>
     */
    public function getMethods();

    /**
     * Returns the targetClass's name.
	 * 返回targetClass的名称
     *
     * @return class-string
     */
    public function getName();

    /**
     * Returns the targetClass's namespace name.
	 * 返回targetClass的名称空间名称
     *
     * @return string
     */
    public function getNamespaceName();

    /**
     * Returns the targetClass's short name.
	 * 返回targetClass的短名称
     *
     * @return string
     */
    public function getShortName();

    /**
     * Returns whether the targetClass has
     * an internal ancestor.
     *
     * @return bool
     */
    public function hasInternalAncestor();

    /**
     * Returns whether the targetClass is in
     * the passed interface.
     *
     * @param class-string|string $interface
     *
     * @return bool
     */
    public function implementsInterface($interface);

    /**
     * Returns whether the targetClass is in namespace.
	 * 返回targetClass是否在名称空间中
     *
     * @return bool
     */
    public function inNamespace();

    /**
     * Returns whether the targetClass is abstract.
	 * 返回目标类是抽象的
     *
     * @return bool
     */
    public function isAbstract();

    /**
     * Returns whether the targetClass is final.
	 * 返回目标类是否最终
     *
     * @return bool
     */
    public function isFinal();
}
