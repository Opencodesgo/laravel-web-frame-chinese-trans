<?php
/**
 * Illuminate，视图，视图服务提供程序
 */

namespace Illuminate\View;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\FileEngine;
use Illuminate\View\Engines\PhpEngine;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
	 * 注册服务提供程序
     *
     * @return void
     */
    public function register()
    {
        $this->registerFactory();
        $this->registerViewFinder();
        $this->registerBladeCompiler();
        $this->registerEngineResolver();

        $this->app->terminating(static function () {
            Component::flushCache();
        });
    }

    /**
     * Register the view environment.
	 * 注册视图环境
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->app->singleton('view', function ($app) {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
			// 接下来，我们需要获取引擎解析器实例。
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $factory = $this->createFactory($resolver, $finder, $app['events']);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
			// 我们还将在这个视图环境中设置容器实例。
            $factory->setContainer($app);

            $factory->share('app', $app);

            $app->terminating(static function () {
                Component::forgetFactory();
            });

            return $factory;
        });
    }

    /**
     * Create a new Factory Instance.
	 * 创建新的工厂实例
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return \Illuminate\View\Factory
     */
    protected function createFactory($resolver, $finder, $events)
    {
        return new Factory($resolver, $finder, $events);
    }

    /**
     * Register the view finder implementation.
	 * 注册取景器实现
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        });
    }

    /**
     * Register the Blade compiler implementation.、
	 * 注册Blade编译器实现
     *
     * @return void
     */
    public function registerBladeCompiler()
    {
        $this->app->singleton('blade.compiler', function ($app) {
            return tap(new BladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
            ), function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });
    }

    /**
     * Register the engine resolver instance.
	 * 注册引擎解析程序实例
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->app->singleton('view.engine.resolver', function () {
            $resolver = new EngineResolver;

            // Next, we will register the various view engines with the resolver so that the
            // environment will resolve the engines needed for various views based on the
            // extension of view file. We call a method for each of the view's engines.
			// 接下来，我们将向解析器注册各种视图引擎。
			// 以便环境将解决各种视图所需的扩展视图文件引擎。
            foreach (['file', 'php', 'blade'] as $engine) {
                $this->{'register'.ucfirst($engine).'Engine'}($resolver);
            }

            return $resolver;
        });
    }

    /**
     * Register the file engine implementation.
	 * 注册文件引擎实现
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerFileEngine($resolver)
    {
        $resolver->register('file', function () {
            return new FileEngine($this->app['files']);
        });
    }

    /**
     * Register the PHP engine implementation.
	 * 注册PHP引擎实现
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine($this->app['files']);
        });
    }

    /**
     * Register the Blade engine implementation.
	 * 注册Blade引擎实现
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $resolver->register('blade', function () {
            $compiler = new CompilerEngine($this->app['blade.compiler'], $this->app['files']);

            $this->app->terminating(static function () use ($compiler) {
                $compiler->forgetCompiledOrNotExpired();
            });

            return $compiler;
        });
    }
}
