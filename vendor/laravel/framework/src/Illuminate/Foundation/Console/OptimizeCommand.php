<?php
/**
 * Illuminate，基础，控制台，optimize 优化命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'optimize')]
class OptimizeCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'optimize';

    /**
     * The name of the console command.
	 * 控制台命令名称
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'optimize';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Cache the framework bootstrap files';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $this->components->info('Caching the framework bootstrap files');

        collect([
            'config' => fn () => $this->callSilent('config:cache') == 0,
            'routes' => fn () => $this->callSilent('route:cache') == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();
    }
}
