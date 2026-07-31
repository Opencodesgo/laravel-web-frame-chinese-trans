<?php
/**
 * Carbon，特性，宏指令
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
 * Trait Macros.
 * 宏指令特征。
 *
 * Allows users to register macros within the Carbon class.
 * 允许用户在Carbon类中注册宏。
 */
trait Macro
{
    use Mixin;

    /**
     * The registered macros.
	 * 已注册的宏
     *
     * @var array
     */
    protected static $globalMacros = [];

    /**
     * The registered generic macros.
	 * 注册的通用宏
     *
     * @var array
     */
    protected static $globalGenericMacros = [];

    /**
     * Register a custom macro.
	 * 注册自定义宏
     *
     * @example
     * ```
     * $userSettings = [
     *   'locale' => 'pt',
     *   'timezone' => 'America/Sao_Paulo',
     * ];
     * Carbon::macro('userFormat', function () use ($userSettings) {
     *   return $this->copy()->locale($userSettings['locale'])->tz($userSettings['timezone'])->calendar();
     * });
     * echo Carbon::yesterday()->hours(11)->userFormat();
     * ```
     *
     * @param string          $name
     * @param object|callable $macro
     *
     * @return void
     */
    public static function macro($name, $macro)
    {
        static::$globalMacros[$name] = $macro;
    }

    /**
     * Remove all macros and generic macros.
	 * 删除所有宏和通用宏
     */
    public static function resetMacros()
    {
        static::$globalMacros = [];
        static::$globalGenericMacros = [];
    }

    /**
     * Register a custom macro.
	 * 注册自定义宏
     *
     * @param object|callable $macro
     * @param int             $priority marco with higher priority is tried first
     *
     * @return void
     */
    public static function genericMacro($macro, $priority = 0)
    {
        if (!isset(static::$globalGenericMacros[$priority])) {
            static::$globalGenericMacros[$priority] = [];
            krsort(static::$globalGenericMacros, SORT_NUMERIC);
        }

        static::$globalGenericMacros[$priority][] = $macro;
    }

    /**
     * Checks if macro is registered globally.
	 * 检查是否全局注册了宏
     *
     * @param string $name
     *
     * @return bool
     */
    public static function hasMacro($name)
    {
        return isset(static::$globalMacros[$name]);
    }

    /**
     * Get the raw callable macro registered globally for a given name.
     *
     * @param string $name
     *
     * @return callable|null
     */
    public static function getMacro($name)
    {
        return static::$globalMacros[$name] ?? null;
    }

    /**
     * Checks if macro is registered globally or locally.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasLocalMacro($name)
    {
        return ($this->localMacros && isset($this->localMacros[$name])) || static::hasMacro($name);
    }

    /**
     * Get the raw callable macro registered globally or locally for a given name.
	 * 获取为给定名称全局或本地注册的原始可调用宏
     *
     * @param string $name
     *
     * @return callable|null
     */
    public function getLocalMacro($name)
    {
        return ($this->localMacros ?? [])[$name] ?? static::getMacro($name);
    }
}
