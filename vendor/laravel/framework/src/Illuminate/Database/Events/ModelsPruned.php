<?php
/**
 * Illuminate，数据库，事件，模型修剪
 */

namespace Illuminate\Database\Events;

class ModelsPruned
{
    /**
     * The class name of the model that was pruned.
	 * 被修剪的模型的类名
     *
     * @var string
     */
    public $model;

    /**
     * The number of pruned records.
	 * 已修剪记录的数量
     *
     * @var int
     */
    public $count;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  string  $model
     * @param  int  $count
     * @return void
     */
    public function __construct($model, $count)
    {
        $this->model = $model;
        $this->count = $count;
    }
}
