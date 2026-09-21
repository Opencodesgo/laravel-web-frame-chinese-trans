<?php
/**
 * Illuminate，数据库，延迟加载冲突异常
 */

namespace Illuminate\Database;

use RuntimeException;

class LazyLoadingViolationException extends RuntimeException
{
    /**
     * The name of the affected Eloquent model.
	 * 受影响的Eloquent模型的名称
     *
     * @var string
     */
    public $model;

    /**
     * The name of the relation.
	 * 关系的名称
     *
     * @var string
     */
    public $relation;

    /**
     * Create a new exception instance.
	 * 创建一个新的异常实例
     *
     * @param  object  $model
     * @param  string  $relation
     * @return static
     */
    public function __construct($model, $relation)
    {
        $class = get_class($model);

        parent::__construct("Attempted to lazy load [{$relation}] on model [{$class}] but lazy loading is disabled.");

        $this->model = $class;
        $this->relation = $relation;
    }
}
