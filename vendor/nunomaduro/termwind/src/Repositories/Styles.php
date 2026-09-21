<?php
/**
 * Termwind，资源库，样式
 */

declare(strict_types=1);

namespace Termwind\Repositories;

use Closure;
use Termwind\ValueObjects\Style;
use Termwind\ValueObjects\Styles as StylesValueObject;

/**
 * @internal
 */
final class Styles
{
    /**
     * @var array<string, Style>
     */
    private static array $storage = [];

    /**
     * Creates a new style from the given arguments.
	 * 根据给定的参数创建新样式
     *
     * @param  (Closure(StylesValueObject $element, string|int ...$arguments): StylesValueObject)|null  $callback
     */
    public static function create(string $name, ?Closure $callback = null): Style
    {
        self::$storage[$name] = $style = new Style(
            $callback ?? static fn (StylesValueObject $styles) => $styles
        );

        return $style;
    }

    /**
     * Removes all existing styles.
	 * 移除所有现有样式
     */
    public static function flush(): void
    {
        self::$storage = [];
    }

    /**
     * Checks a style with the given name exists.
	 * 检查具有给定名称的样式是否存在
     */
    public static function has(string $name): bool
    {
        return array_key_exists($name, self::$storage);
    }

    /**
     * Gets the style with the given name.
	 * 获取具有给定名称的样式
     */
    public static function get(string $name): Style
    {
        return self::$storage[$name];
    }
}
