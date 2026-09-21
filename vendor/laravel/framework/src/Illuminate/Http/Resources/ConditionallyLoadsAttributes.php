<?php
/**
 * Illuminate，Http，资源，条件加载属性
 */

namespace Illuminate\Http\Resources;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ConditionallyLoadsAttributes
{
    /**
     * Filter the given data, removing any optional values.
	 * 过滤给定的数据，删除任何可选值。
     *
     * @param  array  $data
     * @return array
     */
    protected function filter($data)
    {
        $index = -1;

        foreach ($data as $key => $value) {
            $index++;

            if (is_array($value)) {
                $data[$key] = $this->filter($value);

                continue;
            }

            if (is_numeric($key) && $value instanceof MergeValue) {
                return $this->mergeData(
                    $data, $index, $this->filter($value->data),
                    array_values($value->data) === $value->data
                );
            }

            if ($value instanceof self && is_null($value->resource)) {
                $data[$key] = null;
            }
        }

        return $this->removeMissingValues($data);
    }

    /**
     * Merge the given data in at the given index.
	 * 在给定的索引处合并给定的数据
     *
     * @param  array  $data
     * @param  int  $index
     * @param  array  $merge
     * @param  bool  $numericKeys
     * @return array
     */
    protected function mergeData($data, $index, $merge, $numericKeys)
    {
        if ($numericKeys) {
            return $this->removeMissingValues(array_merge(
                array_merge(array_slice($data, 0, $index, true), $merge),
                $this->filter(array_values(array_slice($data, $index + 1, null, true)))
            ));
        }

        return $this->removeMissingValues(array_slice($data, 0, $index, true) +
                $merge +
                $this->filter(array_slice($data, $index + 1, null, true)));
    }

    /**
     * Remove the missing values from the filtered data.
	 * 从过滤的数据中删除缺失的值
     *
     * @param  array  $data
     * @return array
     */
    protected function removeMissingValues($data)
    {
        $numericKeys = true;

        foreach ($data as $key => $value) {
            if (($value instanceof PotentiallyMissing && $value->isMissing()) ||
                ($value instanceof self &&
                $value->resource instanceof PotentiallyMissing &&
                $value->isMissing())) {
                unset($data[$key]);
            } else {
                $numericKeys = $numericKeys && is_numeric($key);
            }
        }

        if (property_exists($this, 'preserveKeys') && $this->preserveKeys === true) {
            return $data;
        }

        return $numericKeys ? array_values($data) : $data;
    }

    /**
     * Retrieve a value if the given "condition" is truthy.
	 * 如果给定的"条件"为真，检索一个值。
     *
     * @param  bool  $condition
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function when($condition, $value, $default = null)
    {
        if ($condition) {
            return value($value);
        }

        return func_num_args() === 3 ? value($default) : new MissingValue;
    }

    /**
     * Retrieve a value if the given "condition" is falsy.
	 * 如果给定的"条件"为假，则检索一个值。
     *
     * @param  bool  $condition
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    public function unless($condition, $value, $default = null)
    {
        $arguments = func_num_args() === 2 ? [$value] : [$value, $default];

        return $this->when(! $condition, ...$arguments);
    }

    /**
     * Merge a value into the array.
	 * 将值合并到数组中
     *
     * @param  mixed  $value
     * @return \Illuminate\Http\Resources\MergeValue|mixed
     */
    protected function merge($value)
    {
        return $this->mergeWhen(true, $value);
    }

    /**
     * Merge a value if the given condition is truthy.
	 * 如果给定条件为真，则合并一个值。
     *
     * @param  bool  $condition
     * @param  mixed  $value
     * @return \Illuminate\Http\Resources\MergeValue|mixed
     */
    protected function mergeWhen($condition, $value)
    {
        return $condition ? new MergeValue(value($value)) : new MissingValue;
    }

    /**
     * Merge a value unless the given condition is truthy.
	 * 合并一个值，除非给定的条件为真。
     *
     * @param  bool  $condition
     * @param  mixed  $value
     * @return \Illuminate\Http\Resources\MergeValue|mixed
     */
    protected function mergeUnless($condition, $value)
    {
        return ! $condition ? new MergeValue(value($value)) : new MissingValue;
    }

    /**
     * Merge the given attributes.
	 * 合并给定的属性
     *
     * @param  array  $attributes
     * @return \Illuminate\Http\Resources\MergeValue
     */
    protected function attributes($attributes)
    {
        return new MergeValue(
            Arr::only($this->resource->toArray(), $attributes)
        );
    }

    /**
     * Retrieve an attribute if it exists on the resource.
	 * 检索资源上存在的属性
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    public function whenHas($attribute, $value = null, $default = null)
    {
        if (func_num_args() < 3) {
            $default = new MissingValue;
        }

        if (! array_key_exists($attribute, $this->resource->getAttributes())) {
            return value($default);
        }

        return func_num_args() === 1
                ? $this->resource->{$attribute}
                : value($value, $this->resource->{$attribute});
    }

    /**
     * Retrieve a model attribute if it is null.
	 * 如果模型属性为空，则检索该属性。
     *
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenNull($value, $default = null)
    {
        $arguments = func_num_args() == 1 ? [$value] : [$value, $default];

        return $this->when(is_null($value), ...$arguments);
    }

    /**
     * Retrieve a model attribute if it is not null.
	 * 如果模型属性不为空，则检索该属性。
     *
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenNotNull($value, $default = null)
    {
        $arguments = func_num_args() == 1 ? [$value] : [$value, $default];

        return $this->when(! is_null($value), ...$arguments);
    }

    /**
     * Retrieve an accessor when it has been appended.
	 * 在追加访问器时检索访问器
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenAppended($attribute, $value = null, $default = null)
    {
        if ($this->resource->hasAppended($attribute)) {
            return func_num_args() >= 2 ? value($value) : $this->resource->$attribute;
        }

        return func_num_args() === 3 ? value($default) : new MissingValue;
    }

    /**
     * Retrieve a relationship if it has been loaded.
	 * 检索已加载的关系
     *
     * @param  string  $relationship
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenLoaded($relationship, $value = null, $default = null)
    {
        if (func_num_args() < 3) {
            $default = new MissingValue;
        }

        if (! $this->resource->relationLoaded($relationship)) {
            return value($default);
        }

        if (func_num_args() === 1) {
            return $this->resource->{$relationship};
        }

        if ($this->resource->{$relationship} === null) {
            return;
        }

        return value($value);
    }

    /**
     * Retrieve a relationship count if it exists.
	 * 检索存在的关系计数
     *
     * @param  string  $relationship
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    public function whenCounted($relationship, $value = null, $default = null)
    {
        if (func_num_args() < 3) {
            $default = new MissingValue;
        }

        $attribute = (string) Str::of($relationship)->snake()->finish('_count');

        if (! isset($this->resource->getAttributes()[$attribute])) {
            return value($default);
        }

        if (func_num_args() === 1) {
            return $this->resource->{$attribute};
        }

        if ($this->resource->{$attribute} === null) {
            return;
        }

        return value($value, $this->resource->{$attribute});
    }

    /**
     * Execute a callback if the given pivot table has been loaded.
	 * 如果已加载给定的数据透视表，则执行回调。
     *
     * @param  string  $table
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenPivotLoaded($table, $value, $default = null)
    {
        return $this->whenPivotLoadedAs('pivot', ...func_get_args());
    }

    /**
     * Execute a callback if the given pivot table with a custom accessor has been loaded.
	 * 如果加载了带有自定义访问器的数据透视表，则执行回调。
     *
     * @param  string  $accessor
     * @param  string  $table
     * @param  mixed  $value
     * @param  mixed  $default
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenPivotLoadedAs($accessor, $table, $value, $default = null)
    {
        if (func_num_args() === 3) {
            $default = new MissingValue;
        }

        return $this->when(
            isset($this->resource->$accessor) &&
            ($this->resource->$accessor instanceof $table ||
            $this->resource->$accessor->getTable() === $table),
            ...[$value, $default]
        );
    }

    /**
     * Transform the given value if it is present.
	 * 如果给定值存在，则对其进行转换。
     *
     * @param  mixed  $value
     * @param  callable  $callback
     * @param  mixed  $default
     * @return mixed
     */
    protected function transform($value, callable $callback, $default = null)
    {
        return transform(
            $value, $callback, func_num_args() === 3 ? $default : new MissingValue
        );
    }
}
