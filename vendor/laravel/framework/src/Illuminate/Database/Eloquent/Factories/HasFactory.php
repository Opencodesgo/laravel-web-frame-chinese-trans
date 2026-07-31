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
     * @param  mixed  $parameters
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function factory(...$parameters)
    {
        $factory = static::newFactory() ?: Factory::factoryForModel(get_called_class());

        return $factory
                    ->count(is_numeric($parameters[0] ?? null) ? $parameters[0] : null)
                    ->state(is_array($parameters[0] ?? null) ? $parameters[0] : ($parameters[1] ?? []));
    }

    /**
     * Create a new factory instance for the model.
	 * 为模型创建一个新的工厂实例
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        //
    }
}
