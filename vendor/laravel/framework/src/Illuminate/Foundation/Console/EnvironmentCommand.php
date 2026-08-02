<?php
/**
 * Illuminate，基础，控制台，env 环境命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;

class EnvironmentCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'env';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Display the current framework environment';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $this->line('<info>Current application environment:</info> <comment>'.$this->laravel['env'].'</comment>');
    }
}
