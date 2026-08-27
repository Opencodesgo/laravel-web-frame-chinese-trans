<?php
/**
 * Illuminate，集合，发现多个项目异常
 */

namespace Illuminate\Support;

use RuntimeException;

class MultipleItemsFoundException extends RuntimeException
{
    /**
     * The number of items found.
	 * 找到的项目的数量
     *
     * @var int
     */
    public $count;

    /**
     * Create a new exception instance.
	 * 创建新的异常实例
     *
     * @param  int  $count
     * @param  int  $code
     * @param  \Throwable|null  $previous
     * @return void
     */
    public function __construct($count, $code = 0, $previous = null)
    {
        $this->count = $count;

        parent::__construct("$count items were found.", $code, $previous);
    }

    /**
     * Get the number of items found.
	 * 获取找到的项目的数量
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}
