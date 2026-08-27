<?php
/**
 * Illuminate，基础，测试，问题，与 Redis交互
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
	 * 如果redis不可用，则表明连接失败。
     *
     * @var bool
     */
    private static $connectionFailedOnceWithDefaultsSkip = false;

    /**
     * Redis manager instance.
	 * Redis管理器实例
     *
     * @var \Illuminate\Redis\RedisManager[]
     */
    private $redis;

    /**
     * Setup redis connection.
	 * 建立redis连接
     *
     * @return void
     */
    public function setUpRedis()
    {
        if (! extension_loaded('redis')) {
            $this->markTestSkipped('The redis extension is not installed. Please install the extension to enable '.__CLASS__);
        }

        if (static::$connectionFailedOnceWithDefaultsSkip) {
            $this->markTestSkipped('Trying default host/port failed, please set environment variable REDIS_HOST & REDIS_PORT to enable '.__CLASS__);
        }

        $app = $this->app ?? new Application;
        $host = Env::get('REDIS_HOST', '127.0.0.1');
        $port = Env::get('REDIS_PORT', 6379);

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
                    'name' => 'default',
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
	 * 拆除redis连接
     *
     * @return void
     */
    public function tearDownRedis()
    {
        if (isset($this->redis['phpredis'])) {
            $this->redis['phpredis']->connection()->flushdb();
        }

        foreach ($this->redisDriverProvider() as $driver) {
            if (isset($this->redis[$driver[0]])) {
                $this->redis[$driver[0]]->connection()->disconnect();
            }
        }
    }

    /**
     * Get redis driver provider.
	 * 获取redis驱动程序提供程序
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
