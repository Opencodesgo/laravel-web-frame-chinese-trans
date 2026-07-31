<?php
/**
 * League，CommonMark，扩展，内嵌，嵌入适配器接口
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

namespace League\CommonMark\Extension\Embed;

/**
 * Interface for a service which updates the embed code(s) for the given array of embeds
 * 用于更新给定嵌入数组的嵌入代码的服务的接口
 */
interface EmbedAdapterInterface
{
    /**
     * @param Embed[] $embeds
     */
    public function updateEmbeds(array $embeds): void;
}
