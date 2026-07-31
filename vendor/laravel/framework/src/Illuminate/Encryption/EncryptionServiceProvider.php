<?php
/**
 * Illuminate，加密，加密服务提供者
 */

namespace Illuminate\Encryption;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Opis\Closure\SerializableClosure as OpisSerializableClosure;

class EncryptionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
	 * 注册服务提供者
     *
     * @return void
     */
    public function register()
    {
        $this->registerEncrypter();
        $this->registerOpisSecurityKey();
        $this->registerSerializableClosureSecurityKey();
    }

    /**
     * Register the encrypter.
	 * 注册加密器
     *
     * @return void
     */
    protected function registerEncrypter()
    {
        $this->app->singleton('encrypter', function ($app) {
            $config = $app->make('config')->get('app');

            return new Encrypter($this->parseKey($config), $config['cipher']);
        });
    }

    /**
     * Configure Opis Closure signing for security.
	 * 为安全性配置Opis闭包签名
     *
     * @return void
     *
     * @deprecated Will be removed in a future Laravel version.
     */
    protected function registerOpisSecurityKey()
    {
        if (\PHP_VERSION_ID < 80100) {
            $config = $this->app->make('config')->get('app');

            if (! class_exists(OpisSerializableClosure::class) || empty($config['key'])) {
                return;
            }

            OpisSerializableClosure::setSecretKey($this->parseKey($config));
        }
    }

    /**
     * Configure Serializable Closure signing for security.
	 * 为安全性配置Serializable闭包签名
     *
     * @return void
     */
    protected function registerSerializableClosureSecurityKey()
    {
        $config = $this->app->make('config')->get('app');

        if (! class_exists(SerializableClosure::class) || empty($config['key'])) {
            return;
        }

        SerializableClosure::setSecretKey($this->parseKey($config));
    }

    /**
     * Parse the encryption key.
	 * 解析加密密钥
     *
     * @param  array  $config
     * @return string
     */
    protected function parseKey(array $config)
    {
        if (Str::startsWith($key = $this->key($config), $prefix = 'base64:')) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return $key;
    }

    /**
     * Extract the encryption key from the given configuration.
	 * 从给定的配置中提取加密密钥
     *
     * @param  array  $config
     * @return string
     *
     * @throws \Illuminate\Encryption\MissingAppKeyException
     */
    protected function key(array $config)
    {
        return tap($config['key'], function ($key) {
            if (empty($key)) {
                throw new MissingAppKeyException;
            }
        });
    }
}
