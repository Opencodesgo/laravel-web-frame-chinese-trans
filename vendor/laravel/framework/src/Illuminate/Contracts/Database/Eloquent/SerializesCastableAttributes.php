<?php
/**
 * Illuminate，契约，数据库，Eloquent，序列化可浇注属性
 */

namespace Illuminate\Contracts\Database\Eloquent;

interface SerializesCastableAttributes
{
    /**
     * Serialize the attribute when converting the model to an array.
	 * 在将模型转换为数组时序列化该属性
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function serialize($model, string $key, $value, array $attributes);
}
