<?php
/**
 * League，CommonMark，标准化者，独特 Slug规格化接口
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

namespace League\CommonMark\Normalizer;

interface UniqueSlugNormalizerInterface extends TextNormalizerInterface
{
    public const DISABLED        = false;
    public const PER_ENVIRONMENT = 'environment';
    public const PER_DOCUMENT    = 'document';

    /**
     * Called by the Environment whenever the configured scope changes
	 * 当配置的范围发生变化时，由环境调用。
     *
     * Currently, this will only be called PER_DOCUMENT.
     */
    public function clearHistory(): void;
}
