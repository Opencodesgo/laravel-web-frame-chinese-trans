<?php
/**
 * Illuminate，数据库，Eloquent，工厂，属于多种关系
 */

namespace Illuminate\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BelongsToManyRelationship
{
    /**
     * The related factory instance.
	 * 关联的工厂实例
     *
     * @var \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array
     */
    protected $factory;

    /**
     * The pivot attributes / attribute resolver.
	 * pivot属性/属性解析器
     *
     * @var callable|array
     */
    protected $pivot;

    /**
     * The relationship name.
	 * 关系名称
     *
     * @var string
     */
    protected $relationship;

    /**
     * Create a new attached relationship definition.
	 * 创建一个新的附加关系定义
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Factory|\Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array  $factory
     * @param  callable|array  $pivot
     * @param  string  $relationship
     * @return void
     */
    public function __construct($factory, $pivot, $relationship)
    {
        $this->factory = $factory;
        $this->pivot = $pivot;
        $this->relationship = $relationship;
    }

    /**
     * Create the attached relationship for the given model.
	 * 为给定模型创建附加关系
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function createFor(Model $model)
    {
        Collection::wrap($this->factory instanceof Factory ? $this->factory->create([], $model) : $this->factory)->each(function ($attachable) use ($model) {
            $model->{$this->relationship}()->attach(
                $attachable,
                is_callable($this->pivot) ? call_user_func($this->pivot, $model) : $this->pivot
            );
        });
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
