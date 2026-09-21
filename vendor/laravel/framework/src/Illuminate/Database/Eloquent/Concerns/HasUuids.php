<?php
/**
 * Illuminate，数据库，Eloquent，问题，有UUID
 */

namespace Illuminate\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

trait HasUuids
{
    /**
     * Generate a primary UUID for the model.
	 * 为模型生成一个主UUID
     *
     * @return void
     */
    public static function bootHasUuids()
    {
        static::creating(function (self $model) {
            foreach ($model->uniqueIds() as $column) {
                if (empty($model->{$column})) {
                    $model->{$column} = $model->newUniqueId();
                }
            }
        });
    }

    /**
     * Generate a new UUID for the model.
	 * 为模型生成一个新的UUID
     *
     * @return string
     */
    public function newUniqueId()
    {
        return (string) Str::orderedUuid();
    }

    /**
     * Get the columns that should receive a unique identifier.
	 * 获取应该接收唯一标识符的列
     *
     * @return array
     */
    public function uniqueIds()
    {
        return [$this->getKeyName()];
    }

    /**
     * Retrieve the model for a bound value.
	 * 检索绑定值的模型
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($field && in_array($field, $this->uniqueIds()) && ! Str::isUuid($value)) {
            throw (new ModelNotFoundException)->setModel(get_class($this), $value);
        }

        if (! $field && in_array($this->getRouteKeyName(), $this->uniqueIds()) && ! Str::isUuid($value)) {
            throw (new ModelNotFoundException)->setModel(get_class($this), $value);
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    /**
     * Get the auto-incrementing key type.
	 * 获取自动递增的键类型
     *
     * @return string
     */
    public function getKeyType()
    {
        if (in_array($this->getKeyName(), $this->uniqueIds())) {
            return 'string';
        }

        return $this->keyType;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
	 * 获取指示id是否在递增的值
     *
     * @return bool
     */
    public function getIncrementing()
    {
        if (in_array($this->getKeyName(), $this->uniqueIds())) {
            return false;
        }

        return $this->incrementing;
    }
}
