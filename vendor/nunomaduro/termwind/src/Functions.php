<?php
/**
 * Termwind，函数
 */

declare(strict_types=1);

namespace Termwind;

use Closure;
use Symfony\Component\Console\Output\OutputInterface;
use Termwind\Repositories\Styles as StyleRepository;
use Termwind\ValueObjects\Style;
use Termwind\ValueObjects\Styles;

if (! function_exists('Termwind\renderUsing')) {
    /**
     * Sets the renderer implementation.
	 * 设置渲染器实现
     */
    function renderUsing(?OutputInterface $renderer): void
    {
        Termwind::renderUsing($renderer);
    }
}

if (! function_exists('Termwind\style')) {
    /**
     * Creates a new style.
	 * 创建一个新样式
     *
     * @param  (Closure(Styles $renderable, string|int ...$arguments): Styles)|null  $callback
     */
    function style(string $name, ?Closure $callback = null): Style
    {
        return StyleRepository::create($name, $callback);
    }
}

if (! function_exists('Termwind\render')) {
    /**
     * Render HTML to a string.
     */
    function render(string $html, int $options = OutputInterface::OUTPUT_NORMAL): void
    {
        (new HtmlRenderer)->render($html, $options);
    }
}

if (! function_exists('Termwind\terminal')) {
    /**
     * Returns a Terminal instance.
	 * 返回终端实例
     */
    function terminal(): Terminal
    {
        return new Terminal;
    }
}

if (! function_exists('Termwind\ask')) {
    /**
     * Renders a prompt to the user.
	 * 向用户呈现提示
     *
     * @param  iterable<array-key, string>|null  $autocomplete
     */
    function ask(string $question, ?iterable $autocomplete = null): mixed
    {
        return (new Question)->ask($question, $autocomplete);
    }
}
