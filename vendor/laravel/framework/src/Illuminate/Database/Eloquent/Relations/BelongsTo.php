<?php
/**
 * Illuminate，数据库，Eloquent，关系，属于
 */

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\ComparesRelatedModels;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;

class BelongsTo extends Relation
{
    use ComparesRelatedModels,
        InteractsWithDictionary,
        SupportsDefaultModels;

    /**
     * The child model instance of the relation.
	 * 关系的子模型实例
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $child;

    /**
     * The foreign key of the parent model.
	 * 父模型的外键
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The associated key on the parent model.
	 * 父模型上的关联键
     *
     * @var string
     */
    protected $ownerKey;

    /**
     * The name of the relationship.
	 * 关系的名称
     *
     * @var string
     */
    protected $relationName;

    /**
     * Create a new belongs to relationship instance.
	 * 创建一个新的归属关系实例
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $child
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relationName
     * @return void
     */
    public function __construct(Builder $query, Model $child, $foreignKey, $ownerKey, $relationName)
    {
        $this->ownerKey = $ownerKey;
        $this->relationName = $relationName;
        $this->foreignKey = $foreignKey;

        // In the underlying base relationship class, this variable is referred to as
        // the "parent" since most relationships are not inversed. But, since this
        // one is we will create a "child" variable for much better readability.
		// 在基础基关系类中，该变量被称为。
        $this->child = $child;

        parent::__construct($query, $child);
    }

    /**
     * Get the results of the relationship.
	 * 得到关系的结果
     *
     * @return mixed
     */
    public function getResults()
    {
        if (is_null($this->child->{$this->foreignKey})) {
            return $this->getDefaultFor($this->parent);
        }

        return $this->query->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Set the base constraints on the relation query.
	 * 在关系查询上设置基本约束
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            // For belongs to relationships, which are essentially the inverse of has one
            // or has many relationships, we need to actually query on the primary key
            // of the related models matching on the foreign key that's on a parent.
			// For属于关系，本质上是has的倒数。
            $table = $this->related->getTable();

            $this->query->where($table.'.'.$this->ownerKey, '=', $this->child->{$this->foreignKey});
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
	 * 为关系的即时加载设置约束
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        // We'll grab the primary key name of the related models since it could be set to
        // a non-standard name and not "id". We will then construct the constraint for
        // our eagerly loading query so it returns the proper models from execution.
		// 我们将获取相关模型的主键名。
        $key = $this->related->getTable().'.'.$this->ownerKey;

        $whereIn = $this->whereInMethod($this->related, $this->ownerKey);

        $this->query->{$whereIn}($key, $this->getEagerModelKeys($models));
    }

    /**
     * Gather the keys from an array of related models.
	 * 从相关模型的数组中收集键
     *
     * @param  array  $models
     * @return array
     */
    protected function getEagerModelKeys(array $models)
    {
        $keys = [];

        // First we need to gather all of the keys from the parent models so we know what
        // to query for via the eager loading query. We will add them to an array then
        // execute a "where in" statement to gather up all of those related records.
		// 首先，我们需要从父模型中收集所有的键。
        foreach ($models as $model) {
            if (! is_null($value = $model->{$this->foreignKey})) {
                $keys[] = $value;
            }
        }

        sort($keys);

        return array_values(array_unique($keys));
    }

    /**
     * Initialize the relation on a set of models.
	 * 初始化一组模型上的关系
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
	 * 将急切加载的结果与他们的父母匹配
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $foreign = $this->foreignKey;

        $owner = $this->ownerKey;

        // First we will get to build a dictionary of the child models by their primary
        // key of the relationship, then we can easily match the children back onto
        // the parents using that dictionary and the primary key of the children.
		// 首先，我们将根据子模型的主要元素构建子模型的字典。
        $dictionary = [];

        foreach ($results as $result) {
            $attribute = $this->getDictionaryKey($result->getAttribute($owner));

            $dictionary[$attribute] = $result;
        }

        // Once we have the dictionary constructed, we can loop through all the parents
        // and match back onto their children using these keys of the dictionary and
        // the primary key of the children to map them onto the correct instances.
		// 一旦构造了字典，就可以循环遍历所有父元素。
        foreach ($models as $model) {
            $attribute = $this->getDictionaryKey($model->{$foreign});

            if (isset($dictionary[$attribute])) {
                $model->setRelation($relation, $dictionary[$attribute]);
            }
        }

        return $models;
    }

    /**
     * Associate the model instance to the given parent.
	 * 将模型实例关联到给定的父实例
     *
     * @param  \Illuminate\Database\Eloquent\Model|int|string|null  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function associate($model)
    {
        $ownerKey = $model instanceof Model ? $model->getAttribute($this->ownerKey) : $model;

        $this->child->setAttribute($this->foreignKey, $ownerKey);

        if ($model instanceof Model) {
            $this->child->setRelation($this->relationName, $model);
        } else {
            $this->child->unsetRelation($this->relationName);
        }

        return $this->child;
    }

    /**
     * Dissociate previously associated model from the given parent.
	 * 将先前关联的模型与给定的父模型分离
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function dissociate()
    {
        $this->child->setAttribute($this->foreignKey, null);

        return $this->child->setRelation($this->relationName, null);
    }

    /**
     * Alias of "dissociate" method.
	 * "dissociate"方法的别名
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function disassociate()
    {
        return $this->dissociate();
    }

    /**
     * Add the constraints for a relationship query.
	 * 为关系查询添加约束
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        return $query->select($columns)->whereColumn(
            $this->getQualifiedForeignKeyName(), '=', $query->qualifyColumn($this->ownerKey)
        );
    }

    /**
     * Add the constraints for a relationship query on the same table.
	 * 为同一表上的关系查询添加约束
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->select($columns)->from(
            $query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash()
        );

        $query->getModel()->setTable($hash);

        return $query->whereColumn(
            $hash.'.'.$this->ownerKey, '=', $this->getQualifiedForeignKeyName()
        );
    }

    /**
     * Determine if the related model has an auto-incrementing ID.
	 * 确定相关模型是否具有自动递增的ID
     *
     * @return bool
     */
    protected function relationHasIncrementingId()
    {
        return $this->related->getIncrementing() &&
            in_array($this->related->getKeyType(), ['int', 'integer']);
    }

    /**
     * Make a new related instance for the given model.
	 * 为给定模型创建一个新的相关实例
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance();
    }

    /**
     * Get the child of the relationship.
	 * 得到这段关系的孩子
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Get the foreign key of the relationship.
	 * 获取关系的外键
     *
     * @return string
     */
    public function getForeignKeyName()
    {
        return $this->foreignKey;
    }

    /**
     * Get the fully qualified foreign key of the relationship.
	 * 获取关系的完全限定外键
     *
     * @return string
     */
    public function getQualifiedForeignKeyName()
    {
        return $this->child->qualifyColumn($this->foreignKey);
    }

    /**
     * Get the key value of the child's foreign key.
	 * 获取子节点的外键的键值
     *
     * @return mixed
     */
    public function getParentKey()
    {
        return $this->child->{$this->foreignKey};
    }

    /**
     * Get the associated key of the relationship.
	 * 获取关系的关联键
     *
     * @return string
     */
    public function getOwnerKeyName()
    {
        return $this->ownerKey;
    }

    /**
     * Get the fully qualified associated key of the relationship.
	 * 获取关系的完全限定关联键
     *
     * @return string
     */
    public function getQualifiedOwnerKeyName()
    {
        return $this->related->qualifyColumn($this->ownerKey);
    }

    /**
     * Get the value of the model's associated key.
	 * 获取模型关联键的值
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    protected function getRelatedKeyFrom(Model $model)
    {
        return $model->{$this->ownerKey};
    }

    /**
     * Get the name of the relationship.
	 * 获取关系的名称
     *
     * @return string
     */
    public function getRelationName()
    {
        return $this->relationName;
    }
}
