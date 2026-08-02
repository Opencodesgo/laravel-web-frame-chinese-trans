<?php
/**
 * Illuminate，契约，路由，可路由的URL
 */

namespace Illuminate\Contracts\Routing;

interface UrlRoutable
{
    /**
     * Get the value of the model's route key.
	 * 获取模型的路由键值
     *
     * @return mixed
     */
    public function getRouteKey();

    /**
     * Get the route key for the model.
	 * 获取模型的路由键
     *
     * @return string
     */
    public function getRouteKeyName();

    /**
     * Retrieve the model for a bound value.
	 * 检索绑定值的模型
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null);

    /**
     * Retrieve the child model for a bound value.
	 * 检索绑定值的子模型
     *
     * @param  string  $childType
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding($childType, $value, $field);
}
