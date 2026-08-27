<?php
/**
 * League，CommonMark，转换器接口
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

namespace League\CommonMark;

use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Output\RenderedContentInterface;
use League\Config\Exception\ConfigurationExceptionInterface;

/**
 * Interface for a service which converts content from one format (like Markdown) to another (like HTML).
 * 用于将内容从一种格式(如Markdown)转换为另一种格式(如HTML)的服务的接口。
 */
interface ConverterInterface
{
    /**
     * @throws CommonMarkException
     * @throws ConfigurationExceptionInterface
     */
    public function convert(string $input): RenderedContentInterface;
}
