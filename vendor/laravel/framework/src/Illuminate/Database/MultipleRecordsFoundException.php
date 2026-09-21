<?php
/**
 * Illuminate，数据库，发现多条记录异常
 */

namespace Illuminate\Database;

use RuntimeException;

class MultipleRecordsFoundException extends RuntimeException
{
    /**
     * The number of records found.
	 * 找到的记录数
     *
     * @var int
     */
    public $count;

    /**
     * Create a new exception instance.
	 * 创建一个新的异常实例
     *
     * @param  int  $count
     * @param  int  $code
     * @param  \Throwable|null  $previous
     * @return void
     */
    public function __construct($count, $code = 0, $previous = null)
    {
        $this->count = $count;

        parent::__construct("$count records were found.", $code, $previous);
    }

    /**
     * Get the number of records found.
	 * 获取找到的记录的数量
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}
