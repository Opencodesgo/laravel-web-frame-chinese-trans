<?php
/**
 * Illuminate，数据库，Eloquent，关系未发现异常
 */

namespace Illuminate\Database\Eloquent;

use RuntimeException;

class RelationNotFoundException extends RuntimeException
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
	 * 关系名称
     *
     * @var string
     */
    public $relation;

    /**
     * Create a new exception instance.
	 * 创建新的异常实例
     *
     * @param  object  $model
     * @param  string  $relation
     * @param  string|null  $type
     * @return static
     */
    public static function make($model, $relation, $type = null)
    {
        $class = get_class($model);

        $instance = new static(
            is_null($type)
                ? "Call to undefined relationship [{$relation}] on model [{$class}]."
                : "Call to undefined relationship [{$relation}] on model [{$class}] of type [{$type}].",
        );

        $instance->model = $class;
        $instance->relation = $relation;

        return $instance;
    }
}
