<?php
/**
 * Illuminate，数据库，Eloquent，关系，向许多变形
 */

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class MorphToMany extends BelongsToMany
{
    /**
     * The type of the polymorphic relation.
	 * 多态关系的类型
     *
     * @var string
     */
    protected $morphType;

    /**
     * The class name of the morph type constraint.
	 * 变形类型约束的类名
     *
     * @var string
     */
    protected $morphClass;

    /**
     * Indicates if we are connecting the inverse of the relation.
	 * 指示我们是否连接关系的逆
     *
     * This primarily affects the morphClass constraint.
     *
     * @var bool
     */
    protected $inverse;

    /**
     * Create a new morph to many relationship instance.
	 * 为许多关系实例创建一个新的变形
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $name
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string|null  $relationName
     * @param  bool  $inverse
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $name, $table, $foreignPivotKey,
                                $relatedPivotKey, $parentKey, $relatedKey, $relationName = null, $inverse = false)
    {
        $this->inverse = $inverse;
        $this->morphType = $name.'_type';
        $this->morphClass = $inverse ? $query->getModel()->getMorphClass() : $parent->getMorphClass();

        parent::__construct(
            $query, $parent, $table, $foreignPivotKey,
            $relatedPivotKey, $parentKey, $relatedKey, $relationName
        );
    }

    /**
     * Set the where clause for the relation query.
	 * 为关系查询设置where子句
     *
     * @return $this
     */
    protected function addWhereConstraints()
    {
        parent::addWhereConstraints();

        $this->query->where($this->qualifyPivotColumn($this->morphType), $this->morphClass);

        return $this;
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
        parent::addEagerConstraints($models);

        $this->query->where($this->qualifyPivotColumn($this->morphType), $this->morphClass);
    }

    /**
     * Create a new pivot attachment record.
	 * 创建一个新的枢轴附件记录
     *
     * @param  int  $id
     * @param  bool  $timed
     * @return array
     */
    protected function baseAttachRecord($id, $timed)
    {
        return Arr::add(
            parent::baseAttachRecord($id, $timed), $this->morphType, $this->morphClass
        );
    }

    /**
     * Add the constraints for a relationship count query.
	 * 为关系计数查询添加约束
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        return parent::getRelationExistenceQuery($query, $parentQuery, $columns)->where(
            $this->qualifyPivotColumn($this->morphType), $this->morphClass
        );
    }

    /**
     * Get the pivot models that are currently attached.
	 * 获取当前附加的枢轴模型
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getCurrentlyAttachedPivots()
    {
        return parent::getCurrentlyAttachedPivots()->map(function ($record) {
            return $record instanceof MorphPivot
                            ? $record->setMorphType($this->morphType)
                                     ->setMorphClass($this->morphClass)
                            : $record;
        });
    }

    /**
     * Create a new query builder for the pivot table.
	 * 为数据透视表创建一个新的查询生成器
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function newPivotQuery()
    {
        return parent::newPivotQuery()->where($this->morphType, $this->morphClass);
    }

    /**
     * Create a new pivot model instance.
	 * 创建一个新的pivot模型实例
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return \Illuminate\Database\Eloquent\Relations\Pivot
     */
    public function newPivot(array $attributes = [], $exists = false)
    {
        $using = $this->using;

        $pivot = $using ? $using::fromRawAttributes($this->parent, $attributes, $this->table, $exists)
                        : MorphPivot::fromAttributes($this->parent, $attributes, $this->table, $exists);

        $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey)
              ->setMorphType($this->morphType)
              ->setMorphClass($this->morphClass);

        return $pivot;
    }

    /**
     * Get the pivot columns for the relation.
	 * 得到关系的主列
     *
     * "pivot_" is prefixed at each column for easy removal later.
     *
     * @return array
     */
    protected function aliasedPivotColumns()
    {
        $defaults = [$this->foreignPivotKey, $this->relatedPivotKey, $this->morphType];

        return collect(array_merge($defaults, $this->pivotColumns))->map(function ($column) {
            return $this->qualifyPivotColumn($column).' as pivot_'.$column;
        })->unique()->all();
    }

    /**
     * Get the foreign key "type" name.
	 * 获取外键"类型"名称
     *
     * @return string
     */
    public function getMorphType()
    {
        return $this->morphType;
    }

    /**
     * Get the class name of the parent model.
	 * 获取父模型的类名
     *
     * @return string
     */
    public function getMorphClass()
    {
        return $this->morphClass;
    }

    /**
     * Get the indicator for a reverse relationship.
	 * 获取反向关系的指示符
     *
     * @return bool
     */
    public function getInverse()
    {
        return $this->inverse;
    }
}
