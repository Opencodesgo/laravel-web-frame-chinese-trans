<?php
/**
 * Illuminate，数据库，模式，外键ID列定义
 */

namespace Illuminate\Database\Schema;

use Illuminate\Support\Str;

class ForeignIdColumnDefinition extends ColumnDefinition
{
    /**
     * The schema builder blueprint instance.
	 * 模式构建器蓝图实例
     *
     * @var \Illuminate\Database\Schema\Blueprint
     */
    protected $blueprint;

    /**
     * Create a new foreign ID column definition.
	 * 创建一个新的外部ID列定义
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  array  $attributes
     * @return void
     */
    public function __construct(Blueprint $blueprint, $attributes = [])
    {
        parent::__construct($attributes);

        $this->blueprint = $blueprint;
    }

    /**
     * Create a foreign key constraint on this column referencing the "id" column of the conventionally related table.
	 * 在这个列上创建一个外键约束，引用常规相关表的"id"列。
     *
     * @param  string|null  $table
     * @param  string  $column
     * @return \Illuminate\Database\Schema\ForeignKeyDefinition
     */
    public function constrained($table = null, $column = 'id')
    {
        return $this->references($column)->on($table ?? Str::of($this->name)->beforeLast('_'.$column)->plural());
    }

    /**
     * Specify which column this foreign ID references on another table.
	 * 指定此外部ID在另一个表上引用的列
     *
     * @param  string  $column
     * @return \Illuminate\Database\Schema\ForeignKeyDefinition
     */
    public function references($column)
    {
        return $this->blueprint->foreign($this->name)->references($column);
    }
}
