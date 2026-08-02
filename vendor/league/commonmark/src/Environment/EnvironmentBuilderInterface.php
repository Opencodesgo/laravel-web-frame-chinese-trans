<?php
/**
 * League，CommonMark，环境，环境生成器接口
 */

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Environment;

use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Exception\AlreadyInitializedException;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationProviderInterface;

/**
 * Interface for building the Environment with any extensions, parsers, listeners, etc. that it may need
 * 用于使用环境可能需要的任何扩展、解析器、侦听器等构建环境的接口。
 */
interface EnvironmentBuilderInterface extends ConfigurationProviderInterface
{
    /**
     * Registers the given extension with the Environment
	 * 将给定的扩展注册为环境
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addExtension(ExtensionInterface $extension): EnvironmentBuilderInterface;

    /**
     * Registers the given block start parser with the Environment
	 * 向环境注册给定的块开始解析器
     *
     * @param BlockStartParserInterface $parser   Block parser instance
     * @param int                       $priority Priority (a higher number will be executed earlier)
     *
     * @return $this
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addBlockStartParser(BlockStartParserInterface $parser, int $priority = 0): EnvironmentBuilderInterface;

    /**
     * Registers the given inline parser with the Environment
	 * 向环境注册给定的内联解析器
     *
     * @param InlineParserInterface $parser   Inline parser instance
     * @param int                   $priority Priority (a higher number will be executed earlier)
     *
     * @return $this
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addInlineParser(InlineParserInterface $parser, int $priority = 0): EnvironmentBuilderInterface;

    /**
     * Registers the given delimiter processor with the Environment
	 * 向Environment注册给定的分隔符处理器
     *
     * @param DelimiterProcessorInterface $processor Delimiter processors instance
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addDelimiterProcessor(DelimiterProcessorInterface $processor): EnvironmentBuilderInterface;

    /**
     * Registers the given node renderer with the Environment
	 * 在环境中注册给定的节点渲染器
     *
     * @param string                $nodeClass The fully-qualified node element class name the renderer below should handle
     * @param NodeRendererInterface $renderer  The renderer responsible for rendering the type of element given above
     * @param int                   $priority  Priority (a higher number will be executed earlier)
     *
     * @psalm-param class-string<Node> $nodeClass
     *
     * @return $this
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addRenderer(string $nodeClass, NodeRendererInterface $renderer, int $priority = 0): EnvironmentBuilderInterface;

    /**
     * Registers the given event listener
	 * 注册给定的事件侦听器
     *
     * @param class-string $eventClass Fully-qualified class name of the event this listener should respond to
     * @param callable     $listener   Listener to be executed
     * @param int          $priority   Priority (a higher number will be executed earlier)
     *
     * @return $this
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addEventListener(string $eventClass, callable $listener, int $priority = 0): EnvironmentBuilderInterface;
}
