<?php
/**
 * League，CommonMark，通用标志转换器
 */

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

/**
 * Converts CommonMark-compatible Markdown to HTML.
 * 将commonmark兼容的Markdown转换为HTML。
 */
final class CommonMarkConverter extends MarkdownConverter
{
    /**
     * Create a new Markdown converter pre-configured for CommonMark
	 * 创建一个为CommonMark预先配置的新Markdown转换器
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        parent::__construct($environment);
    }

    public function getEnvironment(): Environment
    {
        \assert($this->environment instanceof Environment);

        return $this->environment;
    }
}
