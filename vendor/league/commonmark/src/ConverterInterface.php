<?php
/**
 * League，CommonMark，转化器接口
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
 * Interface for a service which converts CommonMark to HTML.
 * 用于将CommonMark转换为HTML的服务的接口
 *
 * @deprecated ConverterInterface is deprecated since league/commonmark 1.4, use MarkdownConverterInterface instead
 */
interface ConverterInterface extends MarkdownConverterInterface
{
}
