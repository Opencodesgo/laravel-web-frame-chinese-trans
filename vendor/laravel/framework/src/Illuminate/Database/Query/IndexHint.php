<?php
/**
 * Illuminate，数据库，查询，索引提示
 */

namespace Illuminate\Database\Query;

class IndexHint
{
    /**
     * The type of query hint.
	 * 查询提示的类型
     *
     * @var string
     */
    public $type;

    /**
     * The name of the index.
	 * 索引的名称
     *
     * @var string
     */
    public $index;

    /**
     * Create a new index hint instance.
	 * 创建一个新的索引提示实例
     *
     * @param  string  $type
     * @param  string  $index
     * @return void
     */
    public function __construct($type, $index)
    {
        $this->type = $type;
        $this->index = $index;
    }
}
