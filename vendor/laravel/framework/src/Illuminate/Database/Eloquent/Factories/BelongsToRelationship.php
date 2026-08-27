<?php
/**
 * Illuminate，数据库，Eloquent，工厂，属于关系
 */

namespace Illuminate\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BelongsToRelationship
{
    /**
     * The related factory instance.
	 * 相关的工厂实例
     *
     * @var \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model
     */
    protected $factory;

    /**
     * The relationship name.
	 * 关系名称
     *
     * @var string
     */
    protected $relationship;

    /**
     * The cached, resolved parent instance ID.
	 * 缓存的、已解析的父实例ID。
     *
     * @var mixed
     */
    protected $resolved;

    /**
     * Create a new "belongs to" relationship definition.
	 * 创建一个新的"属于"关系定义
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Database\Eloquent\Model  $factory
     * @param  string  $relationship
     * @return void
     */
    public function __construct($factory, $relationship)
    {
        $this->factory = $factory;
        $this->relationship = $relationship;
    }

    /**
     * Get the parent model attributes and resolvers for the given child model.
	 * 获取给定子模型的父模型属性和解析器
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    public function attributesFor(Model $model)
    {
        $relationship = $model->{$this->relationship}();

        return $relationship instanceof MorphTo ? [
            $relationship->getMorphType() => $this->factory instanceof Factory ? $this->factory->newModel()->getMorphClass() : $this->factory->getMorphClass(),
            $relationship->getForeignKeyName() => $this->resolver($relationship->getOwnerKeyName()),
        ] : [
            $relationship->getForeignKeyName() => $this->resolver($relationship->getOwnerKeyName()),
        ];
    }

    /**
     * Get the deferred resolver for this relationship's parent ID.
	 * 获取此关系的父ID的延迟解析器
     *
     * @param  string|null  $key
     * @return \Closure
     */
    protected function resolver($key)
    {
        return function () use ($key) {
            if (! $this->resolved) {
                $instance = $this->factory instanceof Factory
                    ? ($this->factory->getRandomRecycledModel($this->factory->modelName()) ?? $this->factory->create())
                    : $this->factory;

                return $this->resolved = $key ? $instance->{$key} : $instance->getKey();
            }

            return $this->resolved;
        };
    }

    /**
     * Specify the model instances to always use when creating relationships.
	 * 指定在创建关系时始终使用的模型实例
     *
     * @param  \Illuminate\Support\Collection  $recycle
     * @return $this
     */
    public function recycle($recycle)
    {
        if ($this->factory instanceof Factory) {
            $this->factory = $this->factory->recycle($recycle);
        }

        return $this;
    }
}
