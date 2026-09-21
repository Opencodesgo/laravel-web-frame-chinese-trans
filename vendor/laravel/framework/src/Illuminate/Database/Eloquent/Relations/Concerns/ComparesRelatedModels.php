<?php
/**
 * Illuminate，数据库，Eloquent，关系，问题，比较相关模型
 */

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use Illuminate\Contracts\Database\Eloquent\SupportsPartialRelations;
use Illuminate\Database\Eloquent\Model;

trait ComparesRelatedModels
{
    /**
     * Determine if the model is the related instance of the relationship.
	 * 确定模型是否是关系的相关实例
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return bool
     */
    public function is($model)
    {
        $match = ! is_null($model) &&
               $this->compareKeys($this->getParentKey(), $this->getRelatedKeyFrom($model)) &&
               $this->related->getTable() === $model->getTable() &&
               $this->related->getConnectionName() === $model->getConnectionName();

        if ($match && $this instanceof SupportsPartialRelations && $this->isOneOfMany()) {
            return $this->query
                        ->whereKey($model->getKey())
                        ->exists();
        }

        return $match;
    }

    /**
     * Determine if the model is not the related instance of the relationship.
	 * 确定模型是否不是关系的相关实例
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return bool
     */
    public function isNot($model)
    {
        return ! $this->is($model);
    }

    /**
     * Get the value of the parent model's key.
	 * 获取父模型的键值
     *
     * @return mixed
     */
    abstract public function getParentKey();

    /**
     * Get the value of the model's related key.
	 * 获取模型相关键的值
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    abstract protected function getRelatedKeyFrom(Model $model);

    /**
     * Compare the parent key with the related key.
	 * 将父键与相关键进行比较
     *
     * @param  mixed  $parentKey
     * @param  mixed  $relatedKey
     * @return bool
     */
    protected function compareKeys($parentKey, $relatedKey)
    {
        if (empty($parentKey) || empty($relatedKey)) {
            return false;
        }

        if (is_int($parentKey) || is_int($relatedKey)) {
            return (int) $parentKey === (int) $relatedKey;
        }

        return $parentKey === $relatedKey;
    }
}
