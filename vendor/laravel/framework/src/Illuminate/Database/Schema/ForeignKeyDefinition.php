<?php
/**
 * Illuminate，数据库，语法，外键定义
 */

namespace Illuminate\Database\Schema;

use Illuminate\Support\Fluent;

/**
 * @method ForeignKeyDefinition deferrable(bool $value = true) Set the foreign key as deferrable (PostgreSQL)
 * @method ForeignKeyDefinition initiallyImmediate(bool $value = true) Set the default time to check the constraint (PostgreSQL)
 * @method ForeignKeyDefinition on(string $table) Specify the referenced table
 * @method ForeignKeyDefinition onDelete(string $action) Add an ON DELETE action
 * @method ForeignKeyDefinition onUpdate(string $action) Add an ON UPDATE action
 * @method ForeignKeyDefinition references(string|array $columns) Specify the referenced column(s)
 */
class ForeignKeyDefinition extends Fluent
{
    /**
     * Indicate that updates should cascade.
	 * 指示更新应该级联
     *
     * @return $this
     */
    public function cascadeOnUpdate()
    {
        return $this->onUpdate('cascade');
    }

    /**
     * Indicate that updates should be restricted.
	 * 指示应该限制更新
     *
     * @return $this
     */
    public function restrictOnUpdate()
    {
        return $this->onUpdate('restrict');
    }

    /**
     * Indicate that deletes should cascade.
	 * 指示删除应该级联
     *
     * @return $this
     */
    public function cascadeOnDelete()
    {
        return $this->onDelete('cascade');
    }

    /**
     * Indicate that deletes should be restricted.
	 * 指示应该限制删除
     *
     * @return $this
     */
    public function restrictOnDelete()
    {
        return $this->onDelete('restrict');
    }

    /**
     * Indicate that deletes should set the foreign key value to null.
	 * 指示删除应该将外键值设置为空
     *
     * @return $this
     */
    public function nullOnDelete()
    {
        return $this->onDelete('set null');
    }

    /**
     * Indicate that deletes should have "no action".
	 * 表明删除应该是"无动作"
     *
     * @return $this
     */
    public function noActionOnDelete()
    {
        return $this->onDelete('no action');
    }
}
