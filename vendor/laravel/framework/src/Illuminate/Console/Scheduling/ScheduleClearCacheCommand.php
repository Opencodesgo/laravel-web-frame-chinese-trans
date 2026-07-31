<?php
/**
 * Illuminate，控制台，调度，schedule:clear-cache 计划清空缓存命令
 */

namespace Illuminate\Console\Scheduling;

use Illuminate\Console\Command;

class ScheduleClearCacheCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'schedule:clear-cache';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Delete the cached mutex files created by scheduler';		#删除调度器创建的缓存互斥文件

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function handle(Schedule $schedule)
    {
        $mutexCleared = false;

        foreach ($schedule->events($this->laravel) as $event) {
            if ($event->mutex->exists($event)) {
                $this->line('<info>Deleting mutex for:</info> '.$event->command);

                $event->mutex->forget($event);

                $mutexCleared = true;
            }
        }

        if (! $mutexCleared) {
            $this->info('No mutex files were found.');
        }
    }
}
