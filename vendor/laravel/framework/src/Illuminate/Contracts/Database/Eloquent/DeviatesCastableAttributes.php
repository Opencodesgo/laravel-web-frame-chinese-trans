<?php
/**
 * Illuminate，契约，数据库，Eloquent，偏离可浇注属性
 */

namespace Illuminate\Contracts\Database\Eloquent;

interface DeviatesCastableAttributes
{
    /**
     * Increment the attribute.
	 * 增加属性
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function increment($model, string $key, $value, array $attributes);

    /**
     * Decrement the attribute.
	 * 递减属性
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function decrement($model, string $key, $value, array $attributes);
}
