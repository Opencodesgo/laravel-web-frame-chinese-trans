<?php
/**
 * Illuminate，基础，测试，问题，与Redis交互
 */

namespace Illuminate\Foundation\Testing\Concerns;

use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Env;

trait InteractsWithRedis
{
    /**
     * Indicate connection failed if redis is not available.
	 * 如果redis不可用,则表示连接失败
     *
     * @var bool
     */
    private static $connectionFailedOnceWithDefaultsSkip = false;

    /**
     * Redis manager instance.
	 * Redis manager 实例
     *
     * @var \Illuminate\Redis\RedisManager[]
     */
    private $redis;

    /**
     * Setup redis connection.
	 * 安装redis连接
     *
     * @return void
     */
    public function setUpRedis()
    {
        $app = $this->app ?? new Application;
        $host = Env::get('REDIS_HOST', '127.0.0.1');
        $port = Env::get('REDIS_PORT', 6379);

        if (! extension_loaded('redis')) {
            $this->markTestSkipped('The redis extension is not installed. Please install the extension to enable '.__CLASS__);

            return;
        }

        if (static::$connectionFailedOnceWithDefaultsSkip) {
            $this->markTestSkipped('Trying default host/port failed, please set environment variable REDIS_HOST & REDIS_PORT to enable '.__CLASS__);

            return;
        }

        foreach ($this->redisDriverProvider() as $driver) {
            $this->redis[$driver[0]] = new RedisManager($app, $driver[0], [
                'cluster' => false,
                'options' => [
                    'prefix' => 'test_',
                ],
                'default' => [
                    'host' => $host,
                    'port' => $port,
                    'database' => 5,
                    'timeout' => 0.5,
                ],
            ]);
        }

        try {
            $this->redis['phpredis']->connection()->flushdb();
        } catch (Exception $e) {
            if ($host === '127.0.0.1' && $port === 6379 && Env::get('REDIS_HOST') === null) {
                static::$connectionFailedOnceWithDefaultsSkip = true;
                $this->markTestSkipped('Trying default host/port failed, please set environment variable REDIS_HOST & REDIS_PORT to enable '.__CLASS__);
            }
        }
    }

    /**
     * Teardown redis connection.
	 * 关闭redis连接
     *
     * @return void
     */
    public function tearDownRedis()
    {
        $this->redis['phpredis']->connection()->flushdb();

        foreach ($this->redisDriverProvider() as $driver) {
            $this->redis[$driver[0]]->connection()->disconnect();
        }
    }

    /**
     * Get redis driver provider.
	 * 获得redis驱动程序提供商
     *
     * @return array
     */
    public function redisDriverProvider()
    {
        return [
            ['predis'],
            ['phpredis'],
        ];
    }

    /**
     * Run test if redis is available.
	 * 如果redis可用，运行测试。
     *
     * @param  callable  $callback
     * @return void
     */
    public function ifRedisAvailable($callback)
    {
        $this->setUpRedis();

        $callback();

        $this->tearDownRedis();
    }
}
