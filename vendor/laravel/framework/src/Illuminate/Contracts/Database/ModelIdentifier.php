<?php
/**
 * Illuminate，契约，数据库，模型标识符
 */

namespace Illuminate\Contracts\Database;

class ModelIdentifier
{
    /**
     * The class name of the model.
	 * 模型类名
     *
     * @var string
     */
    public $class;

    /**
     * The unique identifier of the model.
	 * 模型的唯一标识符
     *
     * This may be either a single ID or an array of IDs.
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
     * The class name of the model collection.
	 * 模型集合的类名
     *
     * @var string|null
     */
    public $collectionClass;

    /**
     * Create a new model identifier.
	 * 创建一个新的模型标识符
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

    /**
     * Specify the collection class that should be used when serializing / restoring collections.
	 * 指定序列化/恢复集合时应该使用的集合类
     *
     * @param  string|null  $collectionClass
     * @return $this
     */
    public function useCollectionClass(?string $collectionClass)
    {
        $this->collectionClass = $collectionClass;

        return $this;
    }
}
