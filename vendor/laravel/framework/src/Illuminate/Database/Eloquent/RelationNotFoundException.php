<?php
/**
 * Illuminate，数据库，Eloquent，关联未发现异常
 */

namespace Illuminate\Database\Eloquent;

use RuntimeException;

class RelationNotFoundException extends RuntimeException
{
    /**
     * The name of the affected Eloquent model.
	 * 将范围应用于给定的Eloquent查询生成器
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
    public static function make($model, $relation)
    {
        $class = get_class($model);

        $instance = new static("Call to undefined relationship [{$relation}] on model [{$class}].");	#调用模型[{$class}]上的未定义关系[{$relation}]

        $instance->model = $class;
        $instance->relation = $relation;

        return $instance;
    }
}
