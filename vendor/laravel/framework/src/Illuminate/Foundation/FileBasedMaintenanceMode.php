<?php
/**
 * Illuminate，基础，基于文件的维护模式
 */

namespace Illuminate\Foundation;

use Illuminate\Contracts\Foundation\MaintenanceMode as MaintenanceModeContract;

class FileBasedMaintenanceMode implements MaintenanceModeContract
{
    /**
     * Take the application down for maintenance.
	 * 删除应用程序以进行维护
     *
     * @param  array  $payload
     * @return void
     */
    public function activate(array $payload): void
    {
        file_put_contents(
            $this->path(),
            json_encode($payload, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Take the application out of maintenance.
	 * 将应用程序从维护中移除
     *
     * @return void
     */
    public function deactivate(): void
    {
        if ($this->active()) {
            unlink($this->path());
        }
    }

    /**
     * Determine if the application is currently down for maintenance.
	 * 确定应用程序当前是否关闭以进行维护
     *
     * @return bool
     */
    public function active(): bool
    {
        return file_exists($this->path());
    }

    /**
     * Get the data array which was provided when the application was placed into maintenance.
	 * 获取应用程序进入维护状态时提供的数据数组
     *
     * @return array
     */
    public function data(): array
    {
        return json_decode(file_get_contents($this->path()), true);
    }

    /**
     * Get the path where the file is stored that signals that the application is down for maintenance.
	 * 获取文件存储的路径，该路径表示应用程序已关闭以进行维护。
     *
     * @return string
     */
    protected function path(): string
    {
        return storage_path('framework/down');
    }
}
