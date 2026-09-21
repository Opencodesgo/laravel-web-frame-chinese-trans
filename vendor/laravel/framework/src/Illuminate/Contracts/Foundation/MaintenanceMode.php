<?php
/**
 * Illuminate，契约，基础，维护模式
 */

namespace Illuminate\Contracts\Foundation;

interface MaintenanceMode
{
    /**
     * Take the application down for maintenance.
	 * 删除应用程序以进行维护
     *
     * @param  array  $payload
     * @return void
     */
    public function activate(array $payload): void;

    /**
     * Take the application out of maintenance.
	 * 将应用程序从维护中移除
     *
     * @return void
     */
    public function deactivate(): void;

    /**
     * Determine if the application is currently down for maintenance.
	 * 确定应用程序当前是否关闭以进行维护
     *
     * @return bool
     */
    public function active(): bool;

    /**
     * Get the data array which was provided when the application was placed into maintenance.
	 * 获取应用程序进入维护状态时提供的数据数组
     *
     * @return array
     */
    public function data(): array;
}
