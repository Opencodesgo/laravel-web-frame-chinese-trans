<?php
/**
 * Illuminate，测试，并行测试
 */

namespace Illuminate\Testing;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;

class ParallelTesting
{
    /**
     * The container instance.
	 * 容器实例
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The options resolver callback.
	 * 选项解析器回调
     *
     * @var \Closure|null
     */
    protected $optionsResolver;

    /**
     * The token resolver callback.
	 * 令牌解析器回调
     *
     * @var \Closure|null
     */
    protected $tokenResolver;

    /**
     * All of the registered "setUp" process callbacks.
	 * 所有注册的"setUp"处理回调
     *
     * @var array
     */
    protected $setUpProcessCallbacks = [];

    /**
     * All of the registered "setUp" test case callbacks.
	 * 所有注册的"setUp"测试用例回调
     *
     * @var array
     */
    protected $setUpTestCaseCallbacks = [];

    /**
     * All of the registered "setUp" test database callbacks.
	 * 所有注册的"setUp"测试数据库回调
     *
     * @var array
     */
    protected $setUpTestDatabaseCallbacks = [];

    /**
     * All of the registered "tearDown" process callbacks.
	 * 所有注册的"tearDown"进程回调
     *
     * @var array
     */
    protected $tearDownProcessCallbacks = [];

    /**
     * All of the registered "tearDown" test case callbacks.
	 * 所有注册的"tearDown"测试用例回调
     *
     * @var array
     */
    protected $tearDownTestCaseCallbacks = [];

    /**
     * Create a new parallel testing instance.
	 * 创建新的行测试实例
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Set a callback that should be used when resolving options.
	 * 设置一个在解析选项时应该使用的回调
     *
     * @param  \Closure|null  $callback
     * @return void
     */
    public function resolveOptionsUsing($resolver)
    {
        $this->optionsResolver = $resolver;
    }

    /**
     * Set a callback that should be used when resolving the unique process token.
	 * 设置一个在解析唯一进程令牌时应该使用的回调
     *
     * @param  \Closure|null  $callback
     * @return void
     */
    public function resolveTokenUsing($resolver)
    {
        $this->tokenResolver = $resolver;
    }

    /**
     * Register a "setUp" process callback.
	 * 注册一个"setUp"进程回调
     *
     * @param  callable  $callback
     * @return void
     */
    public function setUpProcess($callback)
    {
        $this->setUpProcessCallbacks[] = $callback;
    }

    /**
     * Register a "setUp" test case callback.
	 * 注册一个"setUp"测试用例回调
     *
     * @param  callable  $callback
     * @return void
     */
    public function setUpTestCase($callback)
    {
        $this->setUpTestCaseCallbacks[] = $callback;
    }

    /**
     * Register a "setUp" test database callback.
	 * 注册一个"setUp"测试数据库回调
     *
     * @param  callable  $callback
     * @return void
     */
    public function setUpTestDatabase($callback)
    {
        $this->setUpTestDatabaseCallbacks[] = $callback;
    }

    /**
     * Register a "tearDown" process callback.
	 * 注册一个"tearDown"进程回调
     *
     * @param  callable  $callback
     * @return void
     */
    public function tearDownProcess($callback)
    {
        $this->tearDownProcessCallbacks[] = $callback;
    }

    /**
     * Register a "tearDown" test case callback.
	 * 注册一个"tearDown"测试用例回调
     *
     * @param  callable  $callback
     * @return void
     */
    public function tearDownTestCase($callback)
    {
        $this->tearDownTestCaseCallbacks[] = $callback;
    }

    /**
     * Call all of the "setUp" process callbacks.
	 * 调用所有的"setUp"进程回调
     *
     * @return void
     */
    public function callSetUpProcessCallbacks()
    {
        $this->whenRunningInParallel(function () {
            foreach ($this->setUpProcessCallbacks as $callback) {
                $this->container->call($callback, [
                    'token' => $this->token(),
                ]);
            }
        });
    }

    /**
     * Call all of the "setUp" test case callbacks.
	 * 调用所有的"setUp"测试用例回调
     *
     * @param  \Illuminate\Foundation\Testing\TestCase  $testCase
     * @return void
     */
    public function callSetUpTestCaseCallbacks($testCase)
    {
        $this->whenRunningInParallel(function () use ($testCase) {
            foreach ($this->setUpTestCaseCallbacks as $callback) {
                $this->container->call($callback, [
                    'testCase' => $testCase,
                    'token' => $this->token(),
                ]);
            }
        });
    }

    /**
     * Call all of the "setUp" test database callbacks.
	 * 调用所有的"setUp"测试数据库回调
     *
     * @param  string  $database
     * @return void
     */
    public function callSetUpTestDatabaseCallbacks($database)
    {
        $this->whenRunningInParallel(function () use ($database) {
            foreach ($this->setUpTestDatabaseCallbacks as $callback) {
                $this->container->call($callback, [
                    'database' => $database,
                    'token' => $this->token(),
                ]);
            }
        });
    }

    /**
     * Call all of the "tearDown" process callbacks.
	 * 调用所有的"tearDown"流程回调
     *
     * @return void
     */
    public function callTearDownProcessCallbacks()
    {
        $this->whenRunningInParallel(function () {
            foreach ($this->tearDownProcessCallbacks as $callback) {
                $this->container->call($callback, [
                    'token' => $this->token(),
                ]);
            }
        });
    }

    /**
     * Call all of the "tearDown" test case callbacks.
	 * 调用所有的"tearDown"测试用例回调
     *
     * @param  \Illuminate\Foundation\Testing\TestCase  $testCase
     * @return void
     */
    public function callTearDownTestCaseCallbacks($testCase)
    {
        $this->whenRunningInParallel(function () use ($testCase) {
            foreach ($this->tearDownTestCaseCallbacks as $callback) {
                $this->container->call($callback, [
                    'testCase' => $testCase,
                    'token' => $this->token(),
                ]);
            }
        });
    }

    /**
     * Get a parallel testing option.
	 * 获得并行测试选项
     *
     * @param  string  $option
     * @return mixed
     */
    public function option($option)
    {
        $optionsResolver = $this->optionsResolver ?: function ($option) {
            $option = 'LARAVEL_PARALLEL_TESTING_'.Str::upper($option);

            return $_SERVER[$option] ?? false;
        };

        return call_user_func($optionsResolver, $option);
    }

    /**
     * Gets a unique test token.
	 * 获取唯一的测试令牌
     *
     * @return string|false
     */
    public function token()
    {
        return $token = $this->tokenResolver
            ? call_user_func($this->tokenResolver)
            : ($_SERVER['TEST_TOKEN'] ?? false);
    }

    /**
     * Apply the callback if tests are running in parallel.
	 * 如果测试并行运行，则应用回调。
     *
     * @param  callable  $callback
     * @return void
     */
    protected function whenRunningInParallel($callback)
    {
        if ($this->inParallel()) {
            $callback();
        }
    }

    /**
     * Indicates if the current tests are been run in parallel.
	 * 指明当前测试是否并行运行
     *
     * @return bool
     */
    protected function inParallel()
    {
        return ! empty($_SERVER['LARAVEL_PARALLEL_TESTING']) && $this->token();
    }
}
