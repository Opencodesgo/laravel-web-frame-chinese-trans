<?php
/**
 * Illuminate，验证，规则
 */

namespace Illuminate\Validation;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\ExcludeIf;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\ImageFile;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\NotIn;
use Illuminate\Validation\Rules\ProhibitedIf;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\Rules\Unique;

class Rule
{
    use Macroable;

    /**
     * Create a new conditional rule set.
	 * 创建一个新的条件规则集
     *
     * @param  callable|bool  $condition
     * @param  array|string|\Closure  $rules
     * @param  array|string|\Closure  $defaultRules
     * @return \Illuminate\Validation\ConditionalRules
     */
    public static function when($condition, $rules, $defaultRules = [])
    {
        return new ConditionalRules($condition, $rules, $defaultRules);
    }

    /**
     * Create a new nested rule set.
	 * 创建一个新的嵌套规则集
     *
     * @param  callable  $callback
     * @return \Illuminate\Validation\NestedRules
     */
    public static function forEach($callback)
    {
        return new NestedRules($callback);
    }

    /**
     * Get a unique constraint builder instance.
	 * 获取唯一约束构建器实例
     *
     * @param  string  $table
     * @param  string  $column
     * @return \Illuminate\Validation\Rules\Unique
     */
    public static function unique($table, $column = 'NULL')
    {
        return new Unique($table, $column);
    }

    /**
     * Get an exists constraint builder instance.
	 * 获取一个已存在的约束生成器实例
     *
     * @param  string  $table
     * @param  string  $column
     * @return \Illuminate\Validation\Rules\Exists
     */
    public static function exists($table, $column = 'NULL')
    {
        return new Exists($table, $column);
    }

    /**
     * Get an in constraint builder instance.
	 * 获取约束生成器实例
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string  $values
     * @return \Illuminate\Validation\Rules\In
     */
    public static function in($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new In(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a not_in constraint builder instance.
	 * 获取一个not_in约束生成器实例
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string  $values
     * @return \Illuminate\Validation\Rules\NotIn
     */
    public static function notIn($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new NotIn(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a required_if constraint builder instance.
	 * 获取required_if约束构建器实例
     *
     * @param  callable|bool  $callback
     * @return \Illuminate\Validation\Rules\RequiredIf
     */
    public static function requiredIf($callback)
    {
        return new RequiredIf($callback);
    }

    /**
     * Get a exclude_if constraint builder instance.
	 * 获取一个exclude_if约束生成器实例
     *
     * @param  callable|bool  $callback
     * @return \Illuminate\Validation\Rules\ExcludeIf
     */
    public static function excludeIf($callback)
    {
        return new ExcludeIf($callback);
    }

    /**
     * Get a prohibited_if constraint builder instance.
	 * 获取一个prohibited_if约束构建器实例
     *
     * @param  callable|bool  $callback
     * @return \Illuminate\Validation\Rules\ProhibitedIf
     */
    public static function prohibitedIf($callback)
    {
        return new ProhibitedIf($callback);
    }

    /**
     * Get an enum constraint builder instance.
	 * 获取枚举约束生成器实例
     *
     * @param  string  $type
     * @return \Illuminate\Validation\Rules\Enum
     */
    public static function enum($type)
    {
        return new Enum($type);
    }

    /**
     * Get a file constraint builder instance.
	 * 获取文件约束生成器实例
     *
     * @return \Illuminate\Validation\Rules\File
     */
    public static function file()
    {
        return new File;
    }

    /**
     * Get an image file constraint builder instance.
	 * 获取映像文件约束构建器实例
     *
     * @return \Illuminate\Validation\Rules\ImageFile
     */
    public static function imageFile()
    {
        return new ImageFile;
    }

    /**
     * Get a dimensions constraint builder instance.
	 * 获取维度约束构建器实例
     *
     * @param  array  $constraints
     * @return \Illuminate\Validation\Rules\Dimensions
     */
    public static function dimensions(array $constraints = [])
    {
        return new Dimensions($constraints);
    }
}
