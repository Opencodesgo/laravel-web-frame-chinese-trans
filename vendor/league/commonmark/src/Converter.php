<?php
/**
 * League，CommonMark，转换器
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

/**
 * Converts CommonMark-compatible Markdown to HTML.
 * 将commonmark兼容的Markdown转换为HTML
 *
 * @deprecated This class is deprecated since league/commonmark 1.4, use MarkdownConverter instead.
 */
class Converter implements ConverterInterface
{
    /**
     * The document parser instance.
	 * 文档解析器实例
     *
     * @var DocParserInterface
     */
    protected $docParser;

    /**
     * The html renderer instance.
	 * html渲染器实例
     *
     * @var ElementRendererInterface
     */
    protected $htmlRenderer;

    /**
     * Create a new commonmark converter instance.
	 * 创建一个新的commonmark转换器实例
     *
     * @param DocParserInterface       $docParser
     * @param ElementRendererInterface $htmlRenderer
     */
    public function __construct(DocParserInterface $docParser, ElementRendererInterface $htmlRenderer)
    {
        if (!($this instanceof MarkdownConverter)) {
            @trigger_error(sprintf('The %s class is deprecated since league/commonmark 1.4, use %s instead.', self::class, MarkdownConverter::class), E_USER_DEPRECATED);
        }

        $this->docParser = $docParser;
        $this->htmlRenderer = $htmlRenderer;
    }

    /**
     * Converts CommonMark to HTML.
     *
     * @param string $commonMark
     *
     * @throws \RuntimeException
     *
     * @return string
     *
     * @api
     */
    public function convertToHtml(string $commonMark): string
    {
        $documentAST = $this->docParser->parse($commonMark);

        return $this->htmlRenderer->renderBlock($documentAST);
    }

    /**
     * Converts CommonMark to HTML.
	 * 创建一个新的commonmark转换器实例
     *
     * @see Converter::convertToHtml
     *
     * @param string $commonMark
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function __invoke(string $commonMark): string
    {
        return $this->convertToHtml($commonMark);
    }
}
