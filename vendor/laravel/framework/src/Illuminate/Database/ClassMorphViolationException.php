<?php
/**
 * Illuminate，数据库，类变形违反异常
 */

namespace Illuminate\Database;

use RuntimeException;

class ClassMorphViolationException extends RuntimeException
{
    /**
     * The name of the affected Eloquent model.
	 * 受影响的Eloquent模型的名称
     *
     * @var string
     */
    public $model;

    /**
     * Create a new exception instance.
	 * 创建一个新的异常实例
     *
     * @param  object  $model
     */
    public function __construct($model)
    {
        $class = get_class($model);

        parent::__construct("No morph map defined for model [{$class}].");

        $this->model = $class;
    }
}
