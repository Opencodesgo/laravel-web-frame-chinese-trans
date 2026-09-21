<?php
/**
 * Termwind，值对象，样式
 */

declare(strict_types=1);

namespace Termwind\ValueObjects;

use Closure;
use Termwind\Actions\StyleToMethod;
use Termwind\Components\Element;
use Termwind\Components\Hr;
use Termwind\Components\Li;
use Termwind\Components\Ol;
use Termwind\Components\Ul;
use Termwind\Enums\Color;
use Termwind\Exceptions\ColorNotFound;
use Termwind\Exceptions\InvalidStyle;
use Termwind\Repositories\Styles as StyleRepository;

use function Termwind\terminal;

/**
 * @internal
 */
final class Styles
{
    /**
     * Finds all the styling on a string.
	 * 查找字符串上的所有样式
     */
    public const STYLING_REGEX = "/\<[\w=#\/\;,:.&,%?-]+\>|\\e\[\d+m/";

    /** @var array<int, string> */
    private array $styles = [];

    private ?Element $element = null;

    /**
     * Creates a Style formatter instance.
	 * 创建样式格式化程序实例
     *
     * @param  array<string, mixed>  $properties
     * @param  array<string, Closure(string, array<string, string|int>, array<string, int[]>): string>  $textModifiers
     * @param  array<string, Closure(string, array<string, string|int>): string>  $styleModifiers
     * @param  string[]  $defaultStyles
     */
    final public function __construct(
        private array $properties = [
            'colors' => [],
            'options' => [],
            'isFirstChild' => false,
        ],
        private array $textModifiers = [],
        private array $styleModifiers = [],
        private array $defaultStyles = []
    ) {}

    /**
     * @return $this
     */
    public function setElement(Element $element): self
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Gets default styles.
	 * 得到默认样式
     *
     * @return string[]
     */
    public function defaultStyles(): array
    {
        return $this->defaultStyles;
    }

    /**
     * Gets the element's style properties.
	 * 获取元素的样式属性
     *
     * @return array<string, mixed>
     */
    final public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Sets the element's style properties.
	 * 设置元素的样式属性
     *
     * @param  array<string, mixed>  $properties
     */
    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Sets the styles to the element.
	 * 为元素设置样式
     */
    final public function setStyle(string $style): self
    {
        $this->styles = array_unique(array_merge($this->styles, [$style]));

        return $this;
    }

    /**
     * Checks if the element has the style.
	 * 检查元素是否具有样式
     */
    final public function hasStyle(string $style): bool
    {
        return in_array($style, $this->styles, true);
    }

    /**
     * Adds a style to the element.
	 * 向元素添加样式
     */
    final public function addStyle(string $style): self
    {
        return StyleToMethod::multiple($this, $style);
    }

    /**
     * Inherit styles from given Styles object.
	 * 从给定的styles对象继承样式
     */
    final public function inheritFromStyles(self $styles): self
    {
        foreach (['ml', 'mr', 'pl', 'pr', 'width', 'minWidth', 'maxWidth', 'spaceY', 'spaceX'] as $style) {
            $this->properties['parentStyles'][$style] = array_merge(
                $this->properties['parentStyles'][$style] ?? [],
                $styles->properties['parentStyles'][$style] ?? []
            );

            $this->properties['parentStyles'][$style][] = $styles->properties['styles'][$style] ?? 0;
        }

        $this->properties['parentStyles']['justifyContent'] = $styles->properties['styles']['justifyContent'] ?? false;

        foreach (['bg', 'fg'] as $colorType) {
            $value = (array) ($this->properties['colors'][$colorType] ?? []);
            $parentValue = (array) ($styles->properties['colors'][$colorType] ?? []);

            if ($value === [] && $parentValue !== []) {
                $this->properties['colors'][$colorType] = $styles->properties['colors'][$colorType];
            }
        }

        if (! is_null($this->properties['options']['bold'] ?? null) ||
            ! is_null($styles->properties['options']['bold'] ?? null)) {
            $this->properties['options']['bold'] = $this->properties['options']['bold']
                ?? $styles->properties['options']['bold']
                ?? false;
        }

        return $this;
    }

    /**
     * Adds a background color to the element.
	 * 为元素添加背景色
     */
    final public function bg(string $color, int $variant = 0): self
    {
        return $this->with(['colors' => [
            'bg' => $this->getColorVariant($color, $variant),
        ]]);
    }

    /**
     * Adds a bold style to the element.
	 * 向元素添加粗体样式
     */
    final public function fontBold(): self
    {
        return $this->with(['options' => [
            'bold' => true,
        ]]);
    }

    /**
     * Removes the bold style on the element.
	 * 移除元素上的粗体样式
     */
    final public function fontNormal(): self
    {
        return $this->with(['options' => [
            'bold' => false,
        ]]);
    }

    /**
     * Adds a bold style to the element.
     */
    final public function strong(): self
    {
        $this->styleModifiers[__METHOD__] = static fn ($text): string => sprintf("\e[1m%s\e[0m", $text);

        return $this;
    }

    /**
     * Adds an italic style to the element.
     */
    final public function italic(): self
    {
        $this->styleModifiers[__METHOD__] = static fn ($text): string => sprintf("\e[3m%s\e[0m", $text);

        return $this;
    }

    /**
     * Adds an underline style.
     */
    final public function underline(): self
    {
        $this->styleModifiers[__METHOD__] = static fn ($text): string => sprintf("\e[4m%s\e[0m", $text);

        return $this;
    }

    /**
     * Adds the given margin left to the element.
     */
    final public function ml(int $margin): self
    {
        return $this->with(['styles' => [
            'ml' => $margin,
        ]]);
    }

    /**
     * Adds the given margin right to the element.
	 * 将给定的边距右加到元素
     */
    final public function mr(int $margin): self
    {
        return $this->with(['styles' => [
            'mr' => $margin,
        ]]);
    }

    /**
     * Adds the given margin bottom to the element.
	 * 将给定的边距底部添加到元素
     */
    final public function mb(int $margin): self
    {
        return $this->with(['styles' => [
            'mb' => $margin,
        ]]);
    }

    /**
     * Adds the given margin top to the element.
	 * 将给定的边距顶部添加到元素
     */
    final public function mt(int $margin): self
    {
        return $this->with(['styles' => [
            'mt' => $margin,
        ]]);
    }

    /**
     * Adds the given horizontal margin to the element.
	 * 将给定的水平边距添加到元素
     */
    final public function mx(int $margin): self
    {
        return $this->with(['styles' => [
            'ml' => $margin,
            'mr' => $margin,
        ]]);
    }

    /**
     * Adds the given vertical margin to the element.
	 * 将给定的垂直边距添加到元素
     */
    final public function my(int $margin): self
    {
        return $this->with(['styles' => [
            'mt' => $margin,
            'mb' => $margin,
        ]]);
    }

    /**
     * Adds the given margin to the element.
	 * 将给定的边距添加到元素
     */
    final public function m(int $margin): self
    {
        return $this->my($margin)->mx($margin);
    }

    /**
     * Adds the given padding left to the element.
	 * 将给定的左padding添加到元素中
     */
    final public function pl(int $padding): static
    {
        return $this->with(['styles' => [
            'pl' => $padding,
        ]]);
    }

    /**
     * Adds the given padding right.
	 * 向右添加给定的填充
     */
    final public function pr(int $padding): static
    {
        return $this->with(['styles' => [
            'pr' => $padding,
        ]]);
    }

    /**
     * Adds the given horizontal padding.
	 * 添加给定的水平填充
     */
    final public function px(int $padding): self
    {
        return $this->pl($padding)->pr($padding);
    }

    /**
     * Adds the given padding top.
	 * 添加给定的填充顶部
     */
    final public function pt(int $padding): static
    {
        return $this->with(['styles' => [
            'pt' => $padding,
        ]]);
    }

    /**
     * Adds the given padding bottom.
	 * 添加给定的填充底部
     */
    final public function pb(int $padding): static
    {
        return $this->with(['styles' => [
            'pb' => $padding,
        ]]);
    }

    /**
     * Adds the given vertical padding.
	 * 添加给定的垂直填充
     */
    final public function py(int $padding): self
    {
        return $this->pt($padding)->pb($padding);
    }

    /**
     * Adds the given padding.
	 * 添加给定的填充
     */
    final public function p(int $padding): self
    {
        return $this->pt($padding)->pr($padding)->pb($padding)->pl($padding);
    }

    /**
     * Adds the given vertical margin to the childs, ignoring the first child.
	 * 将给定的垂直边距添加到子元素，忽略第一个子元素。
     */
    final public function spaceY(int $space): self
    {
        return $this->with(['styles' => [
            'spaceY' => $space,
        ]]);
    }

    /**
     * Adds the given horizontal margin to the childs, ignoring the first child.
	 * 向子元素添加给定的水平边距，忽略第一个子元素。
     */
    final public function spaceX(int $space): self
    {
        return $this->with(['styles' => [
            'spaceX' => $space,
        ]]);
    }

    /**
     * Adds a border on top of each element.
	 * 在每个元素的顶部添加边框
     */
    final public function borderT(int $width = 1): self
    {
        if (! $this->element instanceof Hr) {
            throw new InvalidStyle('`border-t` can only be used on an "hr" element.');
        }

        $this->styleModifiers[__METHOD__] = function ($text, $styles): string {
            $length = $this->getLength($text);
            if ($length < 1) {
                $margins = (int) ($styles['ml'] ?? 0) + ($styles['mr'] ?? 0);

                return str_repeat('─', self::getParentWidth($this->properties['parentStyles'] ?? []) - $margins);
            }

            return str_repeat('─', $length);
        };

        return $this;
    }

    /**
     * Adds a text alignment or color to the element.
	 * 向元素添加文本对齐方式或颜色
     */
    final public function text(string $value, int $variant = 0): self
    {
        if (in_array($value, ['left', 'right', 'center'], true)) {
            return $this->with(['styles' => [
                'text-align' => $value,
            ]]);
        }

        return $this->with(['colors' => [
            'fg' => $this->getColorVariant($value, $variant),
        ]]);
    }

    /**
     * Truncates the text of the element.
	 * 截断元素的文本
     */
    final public function truncate(int $limit = 0, string $end = '…'): self
    {
        $this->textModifiers[__METHOD__] = function ($text, $styles) use ($limit, $end): string {
            $width = $styles['width'] ?? 0;

            if (is_string($width)) {
                $width = self::calcWidthFromFraction(
                    $width,
                    $styles,
                    $this->properties['parentStyles'] ?? []
                );
            }

            [, $paddingRight, , $paddingLeft] = $this->getPaddings();
            $width -= $paddingRight + $paddingLeft;

            $limit = $limit > 0 ? $limit : $width;
            if ($limit === 0) {
                return $text;
            }

            $limit -= mb_strwidth($end, 'UTF-8');

            if ($this->getLength($text) <= $limit) {
                return $text;
            }

            return rtrim(self::trimText($text, $limit).$end);
        };

        return $this;
    }

    /**
     * Forces the width of the element.
	 * 强制元素的宽度
     */
    final public function w(int|string $width): static
    {
        return $this->with(['styles' => [
            'width' => $width,
        ]]);
    }

    /**
     * Forces the element width to the full width of the terminal.
	 * 强制元素宽度为终端的全宽度
     */
    final public function wFull(): static
    {
        return $this->w('1/1');
    }

    /**
     * Removes the width set on the element.
	 * 移除元素上设置的宽度
     */
    final public function wAuto(): static
    {
        return $this->with(['styles' => [
            'width' => null,
        ]]);
    }

    /**
     * Defines a minimum width of an element.
	 * 定义元素的最小宽度
     */
    final public function minW(int|string $width): static
    {
        return $this->with(['styles' => [
            'minWidth' => $width,
        ]]);
    }

    /**
     * Defines a maximum width of an element.
	 * 定义元素的最大宽度
     */
    final public function maxW(int|string $width): static
    {
        return $this->with(['styles' => [
            'maxWidth' => $width,
        ]]);
    }

    /**
     * Makes the element's content uppercase.
	 * 使元素的内容大写
     */
    final public function uppercase(): self
    {
        $this->textModifiers[__METHOD__] = static fn ($text): string => mb_strtoupper($text, 'UTF-8');

        return $this;
    }

    /**
     * Makes the element's content lowercase.
	 * 使元素的内容小写
     */
    final public function lowercase(): self
    {
        $this->textModifiers[__METHOD__] = static fn ($text): string => mb_strtolower($text, 'UTF-8');

        return $this;
    }

    /**
     * Makes the element's content capitalize.
	 * 使元素的内容大写
     */
    final public function capitalize(): self
    {
        $this->textModifiers[__METHOD__] = static fn ($text): string => mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');

        return $this;
    }

    /**
     * Makes the element's content in snakecase.
	 * 使元素的内容为蛇形
     */
    final public function snakecase(): self
    {
        $this->textModifiers[__METHOD__] = static fn ($text): string => mb_strtolower(
            (string) preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $text),
            'UTF-8'
        );

        return $this;
    }

    /**
     * Makes the element's content with a line through.
	 * 使元素的内容以横线贯穿
     */
    final public function lineThrough(): self
    {
        $this->styleModifiers[__METHOD__] = static fn ($text): string => sprintf("\e[9m%s\e[0m", $text);

        return $this;
    }

    /**
     * Makes the element's content invisible.
	 * 使元素的内容不可见
     */
    final public function invisible(): self
    {
        $this->styleModifiers[__METHOD__] = static fn ($text): string => sprintf("\e[8m%s\e[0m", $text);

        return $this;
    }

    /**
     * Do not display element's content.
	 * 不显示元素的内容
     */
    final public function hidden(): self
    {
        return $this->with(['styles' => [
            'display' => 'hidden',
        ]]);
    }

    /**
     * Makes a line break before the element's content.
	 * 在元素内容前换行
     */
    final public function block(): self
    {
        return $this->with(['styles' => [
            'display' => 'block',
        ]]);
    }

    /**
     * Makes an element eligible to work with flex-1 element's style.
	 * 使一个元素有资格使用flex-1元素的样式
     */
    final public function flex(): self
    {
        return $this->with(['styles' => [
            'display' => 'flex',
        ]]);
    }

    /**
     * Makes an element grow and shrink as needed, ignoring the initial size.
	 * 使一个元素有资格使用flex-1元素的样式
     */
    final public function flex1(): self
    {
        return $this->with(['styles' => [
            'flex-1' => true,
        ]]);
    }

    /**
     * Justifies childs along the element with an equal amount of space between.
	 * 沿元素对齐子元素，并在元素之间留出等量的空间。
     */
    final public function justifyBetween(): self
    {
        return $this->with(['styles' => [
            'justifyContent' => 'between',
        ]]);
    }

    /**
     * Justifies childs along the element with an equal amount of space between
     * each item and half around.
     */
    final public function justifyAround(): self
    {
        return $this->with(['styles' => [
            'justifyContent' => 'around',
        ]]);
    }

    /**
     * Justifies childs along the element with an equal amount of space around each item.
	 * 沿元素对齐子元素，并在每个元素周围留出等量的空间。
     */
    final public function justifyEvenly(): self
    {
        return $this->with(['styles' => [
            'justifyContent' => 'evenly',
        ]]);
    }

    /**
     * Justifies childs along the center of the container’s main axis.
	 * 沿着容器主轴的中心对齐子元素
     */
    final public function justifyCenter(): self
    {
        return $this->with(['styles' => [
            'justifyContent' => 'center',
        ]]);
    }

    /**
     * Repeats the string given until it fills all the content.
	 * 重复给定的字符串，直到它填充所有内容。
     */
    final public function contentRepeat(string $string): self
    {
        $string = preg_replace("/\[?'?([^'|\]]+)'?\]?/", '$1', $string) ?? '';

        $this->textModifiers[__METHOD__] = static fn (): string => str_repeat($string, (int) floor(terminal()->width() / mb_strlen($string, 'UTF-8')));

        return $this->with(['styles' => [
            'contentRepeat' => true,
        ]]);
    }

    /**
     * Prepends text to the content.
	 * 在内容前添加文本
     */
    final public function prepend(string $string): self
    {
        $this->textModifiers[__METHOD__] = static fn ($text): string => $string.$text;

        return $this;
    }

    /**
     * Appends text to the content.
	 * 向内容追加文本
     */
    final public function append(string $string): self
    {
        $this->textModifiers[__METHOD__] = static fn ($text): string => $text.$string;

        return $this;
    }

    /**
     * Prepends the list style type to the content.
	 * 将列表样式类型添加到内容之前
     */
    final public function list(string $type, int $index = 0): self
    {
        if (! $this->element instanceof Ul && ! $this->element instanceof Ol && ! $this->element instanceof Li) {
            throw new InvalidStyle(sprintf(
                'Style list-none cannot be used with %s',
                $this->element !== null ? $this->element::class : 'unknown element'
            ));
        }

        if (! $this->element instanceof Li) {
            return $this;
        }

        return match ($type) {
            'square' => $this->prepend('▪ '),
            'disc' => $this->prepend('• '),
            'decimal' => $this->prepend(sprintf('%d. ', $index)),
            default => $this,
        };
    }

    /**
     * Adds the given properties to the element.
	 * 将给定的属性添加到元素
     *
     * @param  array<string, mixed>  $properties
     */
    public function with(array $properties): self
    {
        $this->properties = array_replace_recursive($this->properties, $properties);

        return $this;
    }

    /**
     * Sets the href property to the element.
	 * 为元素设置href属性。
     */
    final public function href(string $href): self
    {
        $href = str_replace('%', '%%', $href);

        return $this->with(['href' => array_filter([$href])]);
    }

    /**
     * Formats a given string.
	 * 格式化给定字符串。
     */
    final public function format(string $content): string
    {
        foreach ($this->textModifiers as $modifier) {
            $content = $modifier(
                $content,
                $this->properties['styles'] ?? [],
                $this->properties['parentStyles'] ?? []
            );
        }

        $content = $this->applyWidth($content);

        foreach ($this->styleModifiers as $modifier) {
            $content = $modifier($content, $this->properties['styles'] ?? []);
        }

        return $this->applyStyling($content);
    }

    /**
     * Get the format string including required styles.
	 * 获取包含所需样式的格式字符串
     */
    private function getFormatString(): string
    {
        $styles = [];

        /** @var array<int, string> $href */
        $href = $this->properties['href'] ?? [];
        if ($href !== []) {
            $styles[] = sprintf('href=%s', array_pop($href));
        }

        $colors = $this->properties['colors'] ?? [];

        foreach ($colors as $option => $content) {
            if (in_array($option, ['fg', 'bg'], true)) {
                $content = is_array($content) ? array_pop($content) : $content;

                $styles[] = "$option=$content";
            }
        }

        $options = $this->properties['options'] ?? [];

        if ($options !== []) {
            $options = array_keys(array_filter(
                $options, fn ($option) => $option === true
            ));
            $styles[] = count($options) > 0
                ? 'options='.implode(',', $options)
                : 'options=,';
        }

        // If there are no styles we don't need extra tags
        if ($styles === []) {
            return '%s%s%s%s%s';
        }

        return '%s<'.implode(';', $styles).'>%s%s%s</>%s';
    }

    /**
     * Get the margins applied to the element.
	 * 获取应用于元素的边距
     *
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function getMargins(): array
    {
        $isFirstChild = (bool) $this->properties['isFirstChild'];

        $spaceY = $this->properties['parentStyles']['spaceY'] ?? [];
        $spaceY = ! $isFirstChild ? end($spaceY) : 0;

        $spaceX = $this->properties['parentStyles']['spaceX'] ?? [];
        $spaceX = ! $isFirstChild ? end($spaceX) : 0;

        return [
            $spaceY > 0 ? $spaceY : $this->properties['styles']['mt'] ?? 0,
            $this->properties['styles']['mr'] ?? 0,
            $this->properties['styles']['mb'] ?? 0,
            $spaceX > 0 ? $spaceX : $this->properties['styles']['ml'] ?? 0,
        ];
    }

    /**
     * Get the paddings applied to the element.
	 * 获取应用于元素的填充
     *
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function getPaddings(): array
    {
        return [
            $this->properties['styles']['pt'] ?? 0,
            $this->properties['styles']['pr'] ?? 0,
            $this->properties['styles']['pb'] ?? 0,
            $this->properties['styles']['pl'] ?? 0,
        ];
    }

    /**
     * It applies the correct width for the content.
	 * 它为内容应用正确的宽度
     */
    private function applyWidth(string $content): string
    {
        $styles = $this->properties['styles'] ?? [];
        $minWidth = $styles['minWidth'] ?? -1;
        $width = max($styles['width'] ?? -1, $minWidth);
        $maxWidth = $styles['maxWidth'] ?? 0;

        if ($width < 0) {
            return $content;
        }

        if ($width === 0) {
            return '';
        }

        if (is_string($width)) {
            $width = self::calcWidthFromFraction(
                $width,
                $styles,
                $this->properties['parentStyles'] ?? []
            );
        }

        if ($maxWidth > 0) {
            $width = min($styles['maxWidth'], $width);
        }

        $width -= ($styles['pl'] ?? 0) + ($styles['pr'] ?? 0);
        $length = $this->getLength($content);

        preg_match_all("/\n+/", $content, $matches);

        $width *= count($matches[0] ?? []) + 1; // @phpstan-ignore-line
        $width += mb_strlen($matches[0][0] ?? '', 'UTF-8');

        if ($length <= $width) {
            $space = $width - $length;

            return match ($styles['text-align'] ?? '') {
                'right' => str_repeat(' ', $space).$content,
                'center' => str_repeat(' ', (int) floor($space / 2)).$content.str_repeat(' ', (int) ceil($space / 2)),
                default => $content.str_repeat(' ', $space),
            };
        }

        return self::trimText($content, $width);
    }

    /**
     * It applies the styling for the content.
	 * 它为内容应用样式
     */
    private function applyStyling(string $content): string
    {
        $display = $this->properties['styles']['display'] ?? 'inline';

        if ($display === 'hidden') {
            return '';
        }

        $isFirstChild = (bool) $this->properties['isFirstChild'];

        [$marginTop, $marginRight, $marginBottom, $marginLeft] = $this->getMargins();
        [$paddingTop, $paddingRight, $paddingBottom, $paddingLeft] = $this->getPaddings();

        $content = (string) preg_replace('/\r[ \t]?/', "\n",
            (string) preg_replace(
                '/\n/',
                str_repeat(' ', $marginRight + $paddingRight)
                ."\n".
                str_repeat(' ', $marginLeft + $paddingLeft),
                $content)
        );

        $formatted = sprintf(
            $this->getFormatString(),
            str_repeat(' ', $marginLeft),
            str_repeat(' ', $paddingLeft),
            $content,
            str_repeat(' ', $paddingRight),
            str_repeat(' ', $marginRight),
        );

        $empty = str_replace(
            $content,
            str_repeat(' ', $this->getLength($content)),
            $formatted
        );

        $items = [];

        if (in_array($display, ['block', 'flex'], true) && ! $isFirstChild) {
            $items[] = "\n";
        }

        if ($marginTop > 0) {
            $items[] = str_repeat("\n", $marginTop);
        }

        if ($paddingTop > 0) {
            $items[] = $empty."\n";
        }

        $items[] = $formatted;

        if ($paddingBottom > 0) {
            $items[] = "\n".$empty;
        }

        if ($marginBottom > 0) {
            $items[] = str_repeat("\n", $marginBottom);
        }

        return implode('', $items);
    }

    /**
     * Get the length of the text provided without the styling tags.
	 * 获取不带样式标记的文本长度
     */
    public function getLength(?string $text = null): int
    {
        return mb_strlen(preg_replace(
            self::STYLING_REGEX,
            '',
            $text ?? $this->element?->toString() ?? ''
        ) ?? '', 'UTF-8');
    }

    /**
     * Get the length of the element without margins.
	 * 获取不含边距的元素的长度
     */
    public function getInnerWidth(): int
    {
        $innerLength = $this->getLength();
        [, $marginRight, , $marginLeft] = $this->getMargins();

        return $innerLength - $marginLeft - $marginRight;
    }

    /**
     * Get the constant variant color from Color class.
	 * 从color类中获取不变的变体颜色
     */
    private function getColorVariant(string $color, int $variant): string
    {
        if ($variant > 0) {
            $color .= '-'.$variant;
        }

        if (StyleRepository::has($color)) {
            return StyleRepository::get($color)->getColor();
        }

        $colorConstant = mb_strtoupper(str_replace('-', '_', $color), 'UTF-8');

        if (! defined(Color::class."::$colorConstant")) {
            throw new ColorNotFound($colorConstant);
        }

        return constant(Color::class."::$colorConstant");
    }

    /**
     * Calculates the width based on the fraction provided.
	 * 根据提供的分数计算宽度
     *
     * @param  array<string, int>  $styles
     * @param  array<string, array<int, int|string>>  $parentStyles
     */
    private static function calcWidthFromFraction(string $fraction, array $styles, array $parentStyles): int
    {
        $width = self::getParentWidth($parentStyles);

        preg_match('/(\d+)\/(\d+)/', $fraction, $matches);

        if (count($matches) !== 3 || $matches[2] === '0') {
            throw new InvalidStyle(sprintf('Style [%s] is invalid.', "w-$fraction"));
        }

        $width = (int) floor($width * $matches[1] / $matches[2]);
        $width -= ($styles['ml'] ?? 0) + ($styles['mr'] ?? 0);

        return $width;
    }

    /**
     * Gets the width of the parent element.
	 * 获取父元素的宽度。
     *
     * @param  array<string, array<int|string>>  $styles
     */
    public static function getParentWidth(array $styles): int
    {
        $width = terminal()->width();
        foreach ($styles['width'] ?? [] as $index => $parentWidth) {
            $minWidth = (int) $styles['minWidth'][$index];
            $maxWidth = (int) $styles['maxWidth'][$index];
            $margins = (int) $styles['ml'][$index] + (int) $styles['mr'][$index];

            $parentWidth = max($parentWidth, $minWidth);

            if ($parentWidth < 1) {
                $parentWidth = $width;
            } elseif (is_int($parentWidth)) {
                $parentWidth += $margins;
            }

            preg_match('/(\d+)\/(\d+)/', (string) $parentWidth, $matches);

            $width = count($matches) !== 3
                ? (int) $parentWidth
                : (int) floor($width * $matches[1] / $matches[2]);

            if ($maxWidth > 0) {
                $width = min($maxWidth, $width);
            }

            $width -= $margins;
            $width -= (int) $styles['pl'][$index] + (int) $styles['pr'][$index];
        }

        return $width;
    }

    /**
     * It trims the text properly ignoring all escape codes and
     * `<bg;fg;options>` tags.
     */
    private static function trimText(string $text, int $width): string
    {
        preg_match_all(self::STYLING_REGEX, $text, $matches, PREG_OFFSET_CAPTURE);
        $text = rtrim(mb_strimwidth(preg_replace(self::STYLING_REGEX, '', $text) ?? '', 0, $width, '', 'UTF-8'));

        // @phpstan-ignore-next-line
        foreach ($matches[0] ?? [] as [$part, $index]) {
            $text = substr($text, 0, $index).$part.substr($text, $index, null);
        }

        return $text;
    }
}
