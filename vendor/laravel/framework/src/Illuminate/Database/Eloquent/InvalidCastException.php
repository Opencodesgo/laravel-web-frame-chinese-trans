<?php
/**
 * Illuminate，数据库，Eloquent，无效强制转换异常
 */

namespace Illuminate\Database\Eloquent;

use RuntimeException;

class InvalidCastException extends RuntimeException
{
    /**
     * The name of the affected Eloquent model.
	 * 受影响的Eloquent模型的名称
     *
     * @var string
     */
    public $model;

    /**
     * The name of the column.
	 * 列的名称
     *
     * @var string
     */
    public $column;

    /**
     * The name of the cast type.
	 * 强制转换类型的名称
     *
     * @var string
     */
    public $castType;

    /**
     * Create a new exception instance.
	 * 创建新的异常实例
     *
     * @param  object  $model
     * @param  string  $column
     * @param  string  $castType
     * @return static
     */
    public function __construct($model, $column, $castType)
    {
        $class = get_class($model);

        parent::__construct("Call to undefined cast [{$castType}] on column [{$column}] in model [{$class}].");

        $this->model = $class;
        $this->column = $column;
        $this->castType = $castType;
    }
}
