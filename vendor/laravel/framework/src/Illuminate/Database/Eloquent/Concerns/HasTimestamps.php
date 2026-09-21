<?php
/**
 * Illuminate，数据库，Eloquent，问题，有时间戳
 */

namespace Illuminate\Database\Eloquent\Concerns;

use Illuminate\Support\Facades\Date;

trait HasTimestamps
{
    /**
     * Indicates if the model should be timestamped.
	 * 指示是否应该对模型进行时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The list of models classes that have timestamps temporarily disabled.
	 * 具有临时禁用时间戳的模型类列表
     *
     * @var array
     */
    protected static $ignoreTimestampsOn = [];

    /**
     * Update the model's update timestamp.
	 * 更新模型的更新时间戳
     *
     * @param  string|null  $attribute
     * @return bool
     */
    public function touch($attribute = null)
    {
        if ($attribute) {
            $this->$attribute = $this->freshTimestamp();

            return $this->save();
        }

        if (! $this->usesTimestamps()) {
            return false;
        }

        $this->updateTimestamps();

        return $this->save();
    }

    /**
     * Update the model's update timestamp without raising any events.
	 * 在不引发任何事件的情况下更新模型的更新时间戳
     *
     * @param  string|null  $attribute
     * @return bool
     */
    public function touchQuietly($attribute = null)
    {
        return static::withoutEvents(fn () => $this->touch($attribute));
    }

    /**
     * Update the creation and update timestamps.
	 * 更新创建和更新时间戳
     *
     * @return $this
     */
    public function updateTimestamps()
    {
        $time = $this->freshTimestamp();

        $updatedAtColumn = $this->getUpdatedAtColumn();

        if (! is_null($updatedAtColumn) && ! $this->isDirty($updatedAtColumn)) {
            $this->setUpdatedAt($time);
        }

        $createdAtColumn = $this->getCreatedAtColumn();

        if (! $this->exists && ! is_null($createdAtColumn) && ! $this->isDirty($createdAtColumn)) {
            $this->setCreatedAt($time);
        }

        return $this;
    }

    /**
     * Set the value of the "created at" attribute.
	 * 设置"created at"属性的值
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        $this->{$this->getCreatedAtColumn()} = $value;

        return $this;
    }

    /**
     * Set the value of the "updated at" attribute.
	 * 设置"updated at"属性的值
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        $this->{$this->getUpdatedAtColumn()} = $value;

        return $this;
    }

    /**
     * Get a fresh timestamp for the model.
	 * 为模型获取一个新的时间戳
     *
     * @return \Illuminate\Support\Carbon
     */
    public function freshTimestamp()
    {
        return Date::now();
    }

    /**
     * Get a fresh timestamp for the model.
	 * 为模型获取一个新的时间戳
     *
     * @return string
     */
    public function freshTimestampString()
    {
        return $this->fromDateTime($this->freshTimestamp());
    }

    /**
     * Determine if the model uses timestamps.
	 * 确定模型是否使用时间戳
     *
     * @return bool
     */
    public function usesTimestamps()
    {
        return $this->timestamps && ! static::isIgnoringTimestamps($this::class);
    }

    /**
     * Get the name of the "created at" column.
	 * 获取"created at"列的名称
     *
     * @return string|null
     */
    public function getCreatedAtColumn()
    {
        return static::CREATED_AT;
    }

    /**
     * Get the name of the "updated at" column.
	 * 获取"updated at"列的名称
     *
     * @return string|null
     */
    public function getUpdatedAtColumn()
    {
        return static::UPDATED_AT;
    }

    /**
     * Get the fully qualified "created at" column.
	 * 获取完全限定的"created at"列
     *
     * @return string|null
     */
    public function getQualifiedCreatedAtColumn()
    {
        return $this->qualifyColumn($this->getCreatedAtColumn());
    }

    /**
     * Get the fully qualified "updated at" column.
	 * 获取完全限定的"updated at"列
     *
     * @return string|null
     */
    public function getQualifiedUpdatedAtColumn()
    {
        return $this->qualifyColumn($this->getUpdatedAtColumn());
    }

    /**
     * Disable timestamps for the current class during the given callback scope.
	 * 在给定的回调范围内禁用当前类的时间戳
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutTimestamps(callable $callback)
    {
        return static::withoutTimestampsOn([static::class], $callback);
    }

    /**
     * Disable timestamps for the given model classes during the given callback scope.
	 * 在给定回调范围内禁用给定模型类的时间戳
     *
     * @param  array  $models
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutTimestampsOn($models, $callback)
    {
        static::$ignoreTimestampsOn = array_values(array_merge(static::$ignoreTimestampsOn, $models));

        try {
            return $callback();
        } finally {
            static::$ignoreTimestampsOn = array_values(array_diff(static::$ignoreTimestampsOn, $models));
        }
    }

    /**
     * Determine if the given model is ignoring timestamps / touches.
	 * 确定给定模型是否忽略时间戳/触摸
     *
     * @param  string|null  $class
     * @return bool
     */
    public static function isIgnoringTimestamps($class = null)
    {
        $class ??= static::class;

        foreach (static::$ignoreTimestampsOn as $ignoredClass) {
            if ($class === $ignoredClass || is_subclass_of($class, $ignoredClass)) {
                return true;
            }
        }

        return false;
    }
}
