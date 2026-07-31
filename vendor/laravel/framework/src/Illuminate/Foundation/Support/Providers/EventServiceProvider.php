<?php
/**
 * Illuminate，基础，支持，提供商，事件服务提供者
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
     * @var array
     */
    protected $listen = [];

    /**
     * The subscriber classes to register.
	 * 要注册的订阅者类
     *
     * @var array
     */
    protected $subscribe = [];

    /**
     * Register the application's event listeners.
	 * 注册应用的事件侦听器
     *
     * @return void
     */
    public function register()
    {
        $this->booting(function () {
            $events = $this->getEvents();

            foreach ($events as $event => $listeners) {
                foreach (array_unique($listeners) as $listener) {
                    Event::listen($event, $listener);
                }
            }

            foreach ($this->subscribe as $subscriber) {
                Event::subscribe($subscriber);
            }
        });
    }

    /**
     * Boot any application services.
	 * 启动任何应用服务
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the events and handlers.
	 * 得到事件和处理程序
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }

    /**
     * Get the discovered events and listeners for the application.
	 * 获取已发现的应用程序事件和监听器
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
	 * 确定是否应该自动发现事件和监听器
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }

    /**
     * Discover the events and listeners for the application.
	 * 发现应用程序的事件和监听器
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
	 * 获取应该用于发现事件的监听器目录
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
