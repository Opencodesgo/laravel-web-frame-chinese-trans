<?php
/**
 * Illuminate，数据库，Eloquent，工厂，有工厂
 */

namespace Illuminate\Database\Eloquent\Factories;

trait HasFactory
{
    /**
     * Get a new factory instance for the model.
	 * 获取模型的新工厂实例
     *
     * @param  callable|array|int|null  $count
     * @param  callable|array  $state
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    public static function factory($count = null, $state = [])
    {
        $factory = static::newFactory() ?: Factory::factoryForModel(get_called_class());

        return $factory
                    ->count(is_numeric($count) ? $count : null)
                    ->state(is_callable($count) || is_array($count) ? $count : $state);
    }

    /**
     * Create a new factory instance for the model.
	 * 为模型创建一个新的工厂实例
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        //
    }
}
