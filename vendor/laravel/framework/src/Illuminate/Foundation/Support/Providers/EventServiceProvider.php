<?php
/**
 * Illuminate，基础，支持，提供商，事件服务提供商
 */

namespace Illuminate\Foundation\Support\Providers;

use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
	 * 应用程序的事件处理程序映射
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];

    /**
     * The subscribers to register.
	 * 订阅者注册
     *
     * @var array
     */
    protected $subscribe = [];

    /**
     * The model observers to register.
	 * 要注册的模型观察者
     *
     * @var array
     */
    protected $observers = [];

    /**
     * Register the application's event listeners.
	 * 注册应用程序的事件侦听器
     *
     * @return void
     */
    public function register()
    {
        $this->booting(function () {
            $events = $this->getEvents();

            foreach ($events as $event => $listeners) {
                foreach (array_unique($listeners, SORT_REGULAR) as $listener) {
                    Event::listen($event, $listener);
                }
            }

            foreach ($this->subscribe as $subscriber) {
                Event::subscribe($subscriber);
            }

            foreach ($this->observers as $model => $observers) {
                $model::observe($observers);
            }
        });
    }

    /**
     * Boot any application services.
	 * 引导任何应用程序服务
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the events and handlers.
	 * 获取事件和处理程序
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }

    /**
     * Get the discovered events and listeners for the application.
	 * 获取已发现的应用程序事件和侦听器
     *
     * @return array
     */
    public function getEvents()
    {
        if ($this->app->eventsAreCached()) {
            $cache = require $this->app->getCachedEventsPath();

            return $cache[get_class($this)] ?? [];
        } else {
            return array_merge_recursive(
                $this->discoveredEvents(),
                $this->listens()
            );
        }
    }

    /**
     * Get the discovered events for the application.
	 * 获取已发现的应用程序事件
     *
     * @return array
     */
    protected function discoveredEvents()
    {
        return $this->shouldDiscoverEvents()
                    ? $this->discoverEvents()
                    : [];
    }

    /**
     * Determine if events and listeners should be automatically discovered.
	 * 确定是否应该自动发现事件和侦听器
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }

    /**
     * Discover the events and listeners for the application.
	 * 发现应用程序的事件和侦听器
     *
     * @return array
     */
    public function discoverEvents()
    {
        return collect($this->discoverEventsWithin())
                    ->reject(function ($directory) {
                        return ! is_dir($directory);
                    })
                    ->reduce(function ($discovered, $directory) {
                        return array_merge_recursive(
                            $discovered,
                            DiscoverEvents::within($directory, $this->eventDiscoveryBasePath())
                        );
                    }, []);
    }

    /**
     * Get the listener directories that should be used to discover events.
	 * 获取应该用于发现事件的侦听器目录
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            $this->app->path('Listeners'),
        ];
    }

    /**
     * Get the base path to be used during event discovery.
	 * 获取要在事件发现期间使用的基本路径
     *
     * @return string
     */
    protected function eventDiscoveryBasePath()
    {
        return base_path();
    }
}
