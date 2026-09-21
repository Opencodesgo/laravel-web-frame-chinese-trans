<?php
/**
 * Illuminate，基础，控制台，optimize:clear 优化清除命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'optimize:clear')]
class OptimizeClearCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'optimize:clear';

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
    protected static $defaultName = 'optimize:clear';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Remove the cached bootstrap files';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $this->components->info('Clearing cached bootstrap files.');

        collect([
            'events' => fn () => $this->callSilent('event:clear') == 0,
            'views' => fn () => $this->callSilent('view:clear') == 0,
            'cache' => fn () => $this->callSilent('cache:clear') == 0,
            'route' => fn () => $this->callSilent('route:clear') == 0,
            'config' => fn () => $this->callSilent('config:clear') == 0,
            'compiled' => fn () => $this->callSilent('clear-compiled') == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();
    }
}
