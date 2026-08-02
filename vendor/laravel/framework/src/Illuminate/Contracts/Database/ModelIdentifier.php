<?php
/**
 * Illuminate，契约，数据库，模型标识符
 */

namespace Illuminate\Contracts\Database;

class ModelIdentifier
{
    /**
     * The class name of the model.
	 * 模型的类名
     *
     * @var string
     */
    public $class;

    /**
     * The unique identifier of the model.
	 * 模型的唯一标识符
     *
     * This may be either a single ID or an array of IDs.
	 * 它可以是单个ID，也可以是一个ID数组。
     *
     * @var mixed
     */
    public $id;

    /**
     * The relationships loaded on the model.
	 * 关系加载到模型上
     *
     * @var array
     */
    public $relations;

    /**
     * The connection name of the model.
	 * 模型的连接名称
     *
     * @var string|null
     */
    public $connection;

    /**
     * Create a new model identifier.
	 * 创建新的模型标识符
     *
     * @param  string  $class
     * @param  mixed  $id
     * @param  array  $relations
     * @param  mixed  $connection
     * @return void
     */
    public function __construct($class, $id, array $relations, $connection)
    {
        $this->id = $id;
        $this->class = $class;
        $this->relations = $relations;
        $this->connection = $connection;
    }
}
