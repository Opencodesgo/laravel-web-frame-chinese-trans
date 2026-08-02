<?php
/**
 * League，CommonMark，扩展，标题永久链接，Heading Permalink
 */

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Extension\HeadingPermalink;

use League\CommonMark\Inline\Element\AbstractInline;

/**
 * Represents an anchor link within a heading
 * 表示标题内的锚链接
 */
final class HeadingPermalink extends AbstractInline
{
    /** @var string */
    private $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
