<?php
/**
 * League，CommonMark，可配置环境接口
 */

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark;

use League\CommonMark\Block\Parser\BlockParserInterface;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

/**
 * Interface for an Environment which can be configured with config settings, parsers, processors, and renderers
 * 可以配置配置设置、解析器、处理器和渲染器的环境接口。
 */
interface ConfigurableEnvironmentInterface extends EnvironmentInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function mergeConfig(array $config = []);

    /**
     * @param array<string, mixed> $config
     *
     * @return void
     *
     * @deprecated in 1.6 and will be removed in 2.0; use mergeConfig() instead
     */
    public function setConfig(array $config = []);

    /**
     * Registers the given extension with the Environment
	 * 将给定的扩展注册为环境
     *
     * @param ExtensionInterface $extension
     *
     * @return ConfigurableEnvironmentInterface
     */
    public function addExtension(ExtensionInterface $extension): ConfigurableEnvironmentInterface;

    /**
     * Registers the given block parser with the Environment
	 * 将给定的块解析器与环境注册
     *
     * @param BlockParserInterface $parser   Block parser instance
     * @param int                  $priority Priority (a higher number will be executed earlier)
     *
     * @return self
     */
    public function addBlockParser(BlockParserInterface $parser, int $priority = 0): ConfigurableEnvironmentInterface;

    /**
     * Registers the given inline parser with the Environment
	 * 在环境中注册给定的内联解析器
     *
     * @param InlineParserInterface $parser   Inline parser instance
     * @param int                   $priority Priority (a higher number will be executed earlier)
     *
     * @return self
     */
    public function addInlineParser(InlineParserInterface $parser, int $priority = 0): ConfigurableEnvironmentInterface;

    /**
     * Registers the given delimiter processor with the Environment
	 * 将给定的分隔符处理器注册为环境
     *
     * @param DelimiterProcessorInterface $processor Delimiter processors instance
     *
     * @return ConfigurableEnvironmentInterface
     */
    public function addDelimiterProcessor(DelimiterProcessorInterface $processor): ConfigurableEnvironmentInterface;

    /**
     * @param string                 $blockClass    The fully-qualified block element class name the renderer below should handle
     * @param BlockRendererInterface $blockRenderer The renderer responsible for rendering the type of element given above
     * @param int                    $priority      Priority (a higher number will be executed earlier)
     *
     * @return self
     */
    public function addBlockRenderer($blockClass, BlockRendererInterface $blockRenderer, int $priority = 0): ConfigurableEnvironmentInterface;

    /**
     * Registers the given inline renderer with the Environment
	 * 将给定的内联渲染器注册到环境中
     *
     * @param string                  $inlineClass The fully-qualified inline element class name the renderer below should handle
     * @param InlineRendererInterface $renderer    The renderer responsible for rendering the type of element given above
     * @param int                     $priority    Priority (a higher number will be executed earlier)
     *
     * @return self
     */
    public function addInlineRenderer(string $inlineClass, InlineRendererInterface $renderer, int $priority = 0): ConfigurableEnvironmentInterface;

    /**
     * Registers the given event listener
	 * 注册给定的事件侦听器
     *
     * @param string   $eventClass Fully-qualified class name of the event this listener should respond to
     * @param callable $listener   Listener to be executed
     * @param int      $priority   Priority (a higher number will be executed earlier)
     *
     * @return self
     */
    public function addEventListener(string $eventClass, callable $listener, int $priority = 0): ConfigurableEnvironmentInterface;
}
