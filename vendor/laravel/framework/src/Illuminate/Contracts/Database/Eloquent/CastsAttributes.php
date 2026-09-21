<?php
/**
 * Illuminate，契约，数据库，Eloquent，投下属性
 */

namespace Illuminate\Contracts\Database\Eloquent;

/**
 * @template TGet
 * @template TSet
 */
interface CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
	 * 从底层模型值转换属性
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return TGet|null
     */
    public function get($model, string $key, $value, array $attributes);

    /**
     * Transform the attribute to its underlying model values.
	 * 将属性转换为其基础模型值
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  TSet|null  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes);
}
