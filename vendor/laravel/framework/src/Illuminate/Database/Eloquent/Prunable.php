<?php
/**
 * Illuminate，数据库，Eloquent，可删除的
 */

namespace Illuminate\Database\Eloquent;

use Illuminate\Database\Events\ModelsPruned;
use LogicException;

trait Prunable
{
    /**
     * Prune all prunable models in the database.
	 * 删除数据库中所有无法删除的模型
     *
     * @param  int  $chunkSize
     * @return int
     */
    public function pruneAll(int $chunkSize = 1000)
    {
        $total = 0;

        $this->prunable()
            ->when(in_array(SoftDeletes::class, class_uses_recursive(get_class($this))), function ($query) {
                $query->withTrashed();
            })->chunkById($chunkSize, function ($models) use (&$total) {
                $models->each->prune();

                $total += $models->count();

                event(new ModelsPruned(static::class, $total));
            });

        return $total;
    }

    /**
     * Get the prunable model query.
	 * 获取可打印的模型查询
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        throw new LogicException('Please implement the prunable method on your model.');
    }

    /**
     * Prune the model in the database.
	 * 在数据库中修剪模型
     *
     * @return bool|null
     */
    public function prune()
    {
        $this->pruning();

        return in_array(SoftDeletes::class, class_uses_recursive(get_class($this)))
                ? $this->forceDelete()
                : $this->delete();
    }

    /**
     * Prepare the model for pruning.
	 * 准备模型进行修剪
     *
     * @return void
     */
    protected function pruning()
    {
        //
    }
}
