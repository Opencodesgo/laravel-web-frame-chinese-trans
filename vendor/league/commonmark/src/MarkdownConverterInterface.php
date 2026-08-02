<?php
/**
 * League，CommonMark，Markdown 转换接口
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
 * Interface for a service which converts Markdown to HTML.
 * 用于将Markdown转换为HTML的服务的接口
 */
interface MarkdownConverterInterface
{
    /**
     * Converts Markdown to HTML.
	 * 转换Markdown为HTML
     *
     * @param string $markdown
     *
     * @throws \RuntimeException
     *
     * @return string HTML
     *
     * @api
     */
    public function convertToHtml(string $markdown): string;
}
