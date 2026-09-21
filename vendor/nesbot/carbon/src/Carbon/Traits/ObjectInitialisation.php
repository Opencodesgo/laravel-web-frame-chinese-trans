<?php
/**
 * Carbon，特性，对象初始化
 */

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Traits;

trait ObjectInitialisation
{
    /**
     * True when parent::__construct has been called.
	 * 当parent::__construct被调用时为True
     *
     * @var string
     */
    protected $constructedObjectId;
}
