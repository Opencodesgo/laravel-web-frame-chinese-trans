<?php
/**
 * Illuminate，控制台，调度，schedule:test 调度测试命令
 */

namespace Illuminate\Console\Scheduling;

use Illuminate\Console\Command;

class ScheduleTestCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'schedule:test';

    /**
     * The console command description.
	 * 控制台命令描述 
     *
     * @var string
     */
    protected $description = 'Run a scheduled command';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function handle(Schedule $schedule)
    {
        $commands = $schedule->events();

        $commandNames = [];

        foreach ($commands as $command) {
            $commandNames[] = $command->command ?? $command->getSummaryForDisplay();
        }

        $index = array_search($this->choice('Which command would you like to run?', $commandNames), $commandNames);

        $event = $commands[$index];

        $this->line('<info>['.date('c').'] Running scheduled command:</info> '.$event->getSummaryForDisplay());

        $event->run($this->laravel);
    }
}
