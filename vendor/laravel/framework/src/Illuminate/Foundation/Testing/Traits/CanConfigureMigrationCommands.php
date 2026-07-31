<?php
/**
 * Illuminate，基础，测试，可配置迁移命令
 */

namespace Illuminate\Foundation\Testing\Traits;

trait CanConfigureMigrationCommands
{
    /**
     * The parameters that should be used when running "migrate:fresh".
	 * 运行"migrate:fresh"时应该使用的参数
     *
     * @return array
     */
    protected function migrateFreshUsing()
    {
        $seeder = $this->seeder();

        return array_merge(
            [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
            ],
            $seeder ? ['--seeder' => $seeder] : ['--seed' => $this->shouldSeed()]
        );
    }

    /**
     * Determine if views should be dropped when refreshing the database.
	 * 确定在刷新数据库时是否应该删除视图
     *
     * @return bool
     */
    protected function shouldDropViews()
    {
        return property_exists($this, 'dropViews') ? $this->dropViews : false;
    }

    /**
     * Determine if types should be dropped when refreshing the database.
	 * 确定在刷新数据库时是否应该删除类型
     *
     * @return bool
     */
    protected function shouldDropTypes()
    {
        return property_exists($this, 'dropTypes') ? $this->dropTypes : false;
    }

    /**
     * Determine if the seed task should be run when refreshing the database.
	 * 确定在刷新数据库时是否应该运行种子任务
     *
     * @return bool
     */
    protected function shouldSeed()
    {
        return property_exists($this, 'seed') ? $this->seed : false;
    }

    /**
     * Determine the specific seeder class that should be used when refreshing the database.
	 * 确定刷新数据库时应该使用的特定种子程序类
     *
     * @return mixed
     */
    protected function seeder()
    {
        return property_exists($this, 'seeder') ? $this->seeder : false;
    }
}
