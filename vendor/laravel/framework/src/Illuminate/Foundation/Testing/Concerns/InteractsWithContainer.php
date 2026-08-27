<?php
/**
 * Illuminate，基础，测试，问题，与容器交互
 */

namespace Illuminate\Foundation\Testing\Concerns;

use Closure;
use Illuminate\Foundation\Mix;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
use Mockery;

trait InteractsWithContainer
{
    /**
     * The original Vite handler.
	 * 原始的Vite处理程序
     *
     * @var \Illuminate\Foundation\Vite|null
     */
    protected $originalVite;

    /**
     * The original Laravel Mix handler.
	 * 原始的Laravel Mix处理程序
     *
     * @var \Illuminate\Foundation\Mix|null
     */
    protected $originalMix;

    /**
     * Register an instance of an object in the container.
	 * 在容器中注册对象的实例
     *
     * @param  string  $abstract
     * @param  object  $instance
     * @return object
     */
    protected function swap($abstract, $instance)
    {
        return $this->instance($abstract, $instance);
    }

    /**
     * Register an instance of an object in the container.
	 * 在容器中注册对象的实例
     *
     * @param  string  $abstract
     * @param  object  $instance
     * @return object
     */
    protected function instance($abstract, $instance)
    {
        $this->app->instance($abstract, $instance);

        return $instance;
    }

    /**
     * Mock an instance of an object in the container.
	 * 模拟容器中对象的实例
     *
     * @param  string  $abstract
     * @param  \Closure|null  $mock
     * @return \Mockery\MockInterface
     */
    protected function mock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }

    /**
     * Mock a partial instance of an object in the container.
	 * 模拟容器中对象的部分实例
     *
     * @param  string  $abstract
     * @param  \Closure|null  $mock
     * @return \Mockery\MockInterface
     */
    protected function partialMock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args()))->makePartial());
    }

    /**
     * Spy an instance of an object in the container.
	 * 监视容器中对象的实例
     *
     * @param  string  $abstract
     * @param  \Closure|null  $mock
     * @return \Mockery\MockInterface
     */
    protected function spy($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::spy(...array_filter(func_get_args())));
    }

    /**
     * Instruct the container to forget a previously mocked / spied instance of an object.
	 * 指示容器忘记先前模拟/监视的对象实例
     *
     * @param  string  $abstract
     * @return $this
     */
    protected function forgetMock($abstract)
    {
        $this->app->forgetInstance($abstract);

        return $this;
    }

    /**
     * Register an empty handler for Vite in the container.
	 * 在容器中为Vite注册一个空处理程序
     *
     * @return $this
     */
    protected function withoutVite()
    {
        if ($this->originalVite == null) {
            $this->originalVite = app(Vite::class);
        }

        $this->swap(Vite::class, new class
        {
            public function __invoke()
            {
                return '';
            }

            public function __call($name, $arguments)
            {
                return '';
            }

            public function __toString()
            {
                return '';
            }

            public function useIntegrityKey()
            {
                return $this;
            }

            public function useBuildDirectory()
            {
                return $this;
            }

            public function useHotFile()
            {
                return $this;
            }

            public function withEntryPoints()
            {
                return $this;
            }

            public function useScriptTagAttributes()
            {
                return $this;
            }

            public function useStyleTagAttributes()
            {
                return $this;
            }

            public function preloadedAssets()
            {
                return [];
            }
        });

        return $this;
    }

    /**
     * Restore Vite in the container.
	 * 恢复容器中的Vite
     *
     * @return $this
     */
    protected function withVite()
    {
        if ($this->originalVite) {
            $this->app->instance(Vite::class, $this->originalVite);
        }

        return $this;
    }

    /**
     * Register an empty handler for Laravel Mix in the container.
	 * 在容器中为Laravel Mix注册一个空处理程序
     *
     * @return $this
     */
    protected function withoutMix()
    {
        if ($this->originalMix == null) {
            $this->originalMix = app(Mix::class);
        }

        $this->swap(Mix::class, function () {
            return new HtmlString('');
        });

        return $this;
    }

    /**
     * Restore Laravel Mix in the container.
	 * 在容器中恢复Laravel Mix
     *
     * @return $this
     */
    protected function withMix()
    {
        if ($this->originalMix) {
            $this->app->instance(Mix::class, $this->originalMix);
        }

        return $this;
    }
}
