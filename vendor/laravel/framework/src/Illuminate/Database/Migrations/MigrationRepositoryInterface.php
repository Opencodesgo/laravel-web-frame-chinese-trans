<?php
/**
 * Illuminate，数据库，迁移，迁移存储库接口
 */

namespace Illuminate\Database\Migrations;

interface MigrationRepositoryInterface
{
    /**
     * Get the completed migrations.
	 * 获取完成的迁移
     *
     * @return array
     */
    public function getRan();

    /**
     * Get list of migrations.
	 * 得到迁移列表
     *
     * @param  int  $steps
     * @return array
     */
    public function getMigrations($steps);

    /**
     * Get the last migration batch.
	 * 获取最后一个迁移批处理
     *
     * @return array
     */
    public function getLast();

    /**
     * Get the completed migrations with their batch numbers.
	 * 用他们的批号来完成已完成的迁移
     *
     * @return array
     */
    public function getMigrationBatches();

    /**
     * Log that a migration was run.
	 * 运行迁移的日志
     *
     * @param  string  $file
     * @param  int  $batch
     * @return void
     */
    public function log($file, $batch);

    /**
     * Remove a migration from the log.
	 * 从日志中删除迁移
     *
     * @param  object  $migration
     * @return void
     */
    public function delete($migration);

    /**
     * Get the next migration batch number.
	 * 获取下一个迁移批号
     *
     * @return int
     */
    public function getNextBatchNumber();

    /**
     * Create the migration repository data store.
	 * 创建迁移存储库数据存储
     *
     * @return void
     */
    public function createRepository();

    /**
     * Determine if the migration repository exists.
	 * 确定迁移存储库是否存在
     *
     * @return bool
     */
    public function repositoryExists();

    /**
     * Set the information source to gather data.
	 * 设置信息源以收集数据
     *
     * @param  string  $name
     * @return void
     */
    public function setSource($name);
}
