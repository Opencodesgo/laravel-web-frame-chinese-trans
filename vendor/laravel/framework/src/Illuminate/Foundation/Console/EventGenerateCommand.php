<?php
/**
 * Illuminate，基础，控制台，event:generate 事件生成命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'event:generate')]
class EventGenerateCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'event:generate';

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
    protected static $defaultName = 'event:generate';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Generate the missing events and listeners based on registration';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $providers = $this->laravel->getProviders(EventServiceProvider::class);

        foreach ($providers as $provider) {
            foreach ($provider->listens() as $event => $listeners) {
                $this->makeEventAndListeners($event, $listeners);
            }
        }

        $this->components->info('Events and listeners generated successfully.');
    }

    /**
     * Make the event and listeners for the given event.
	 * 为给定事件创建事件和侦听器
     *
     * @param  string  $event
     * @param  array  $listeners
     * @return void
     */
    protected function makeEventAndListeners($event, $listeners)
    {
        if (! str_contains($event, '\\')) {
            return;
        }

        $this->callSilent('make:event', ['name' => $event]);

        $this->makeListeners($event, $listeners);
    }

    /**
     * Make the listeners for the given event.
	 * 为给定事件创建侦听器
     *
     * @param  string  $event
     * @param  array  $listeners
     * @return void
     */
    protected function makeListeners($event, $listeners)
    {
        foreach ($listeners as $listener) {
            $listener = preg_replace('/@.+$/', '', $listener);

            $this->callSilent('make:listener', array_filter(
                ['name' => $listener, '--event' => $event]
            ));
        }
    }
}
