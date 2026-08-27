<?php
/**
 * Termwind，值对象，样式
 */

declare(strict_types=1);

namespace Termwind\ValueObjects;

use Closure;
use Termwind\Actions\StyleToMethod;
use Termwind\Exceptions\InvalidColor;

/**
 * @internal
 */
final class Style
{
    /**
     * Creates a new value object instance.
	 * 创建一个新的值对象实例
     *
     * @param  Closure(Styles $styles, string|int ...$argument): Styles  $callback
     */
    public function __construct(private Closure $callback, private string $color = '')
    {
        // ..
    }

    /**
     * Apply the given set of styles to the styles.
	 * 将给定的一组样式应用到样式上
     */
    public function apply(string $styles): void
    {
        $callback = clone $this->callback;

        $this->callback = static function (
            Styles $formatter,
            string|int ...$arguments
        ) use ($callback, $styles): Styles {
            $formatter = $callback($formatter, ...$arguments);

            return StyleToMethod::multiple($formatter, $styles);
        };
    }

    /**
     * Sets the color to the style.
	 * 将颜色设置为样式
     */
    public function color(string $color): void
    {
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) < 1) {
            throw new InvalidColor(sprintf('The color %s is invalid.', $color));
        }

        $this->color = $color;
    }

    /**
     * Gets the color.
	 * 获取颜色
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Styles the given formatter with this style.
	 * 使用此样式对给定的格式化程序进行样式设置
     */
    public function __invoke(Styles $styles, string|int ...$arguments): Styles
    {
        return ($this->callback)($styles, ...$arguments);
    }
}
