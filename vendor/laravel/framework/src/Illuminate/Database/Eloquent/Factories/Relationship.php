<?php
/**
 * Illuminate，数据库，Eloquent，工厂，关系 
 */

namespace Illuminate\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

class Relationship
{
    /**
     * The related factory instance.
	 * 相关的工厂实例
     *
     * @var \Illuminate\Database\Eloquent\Factories\Factory
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
     * Create a new child relationship instance.
	 * 创建一个新的子关系实例
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Factory  $factory
     * @param  string  $relationship
     * @return void
     */
    public function __construct(Factory $factory, $relationship)
    {
        $this->factory = $factory;
        $this->relationship = $relationship;
    }

    /**
     * Create the child relationship for the given parent model.
	 * 为给定的父模型创建子关系
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @return void
     */
    public function createFor(Model $parent)
    {
        $relationship = $parent->{$this->relationship}();

        if ($relationship instanceof MorphOneOrMany) {
            $this->factory->state([
                $relationship->getMorphType() => $relationship->getMorphClass(),
                $relationship->getForeignKeyName() => $relationship->getParentKey(),
            ])->create([], $parent);
        } elseif ($relationship instanceof HasOneOrMany) {
            $this->factory->state([
                $relationship->getForeignKeyName() => $relationship->getParentKey(),
            ])->create([], $parent);
        } elseif ($relationship instanceof BelongsToMany) {
            $relationship->attach($this->factory->create([], $parent));
        }
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
        $this->factory = $this->factory->recycle($recycle);

        return $this;
    }
}
