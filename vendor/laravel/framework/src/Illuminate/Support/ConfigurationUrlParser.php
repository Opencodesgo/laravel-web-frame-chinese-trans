<?php
/**
 * Illuminate，支持，配置 Url解析器，用来解析数据库连接
 */

namespace Illuminate\Support;

use InvalidArgumentException;

class ConfigurationUrlParser
{
    /**
     * The drivers aliases map.
	 * 驱动程序别名映射
     *
     * @var array
     */
    protected static $driverAliases = [
        'mssql' => 'sqlsrv',
        'mysql2' => 'mysql', // RDS
        'postgres' => 'pgsql',
        'postgresql' => 'pgsql',
        'sqlite3' => 'sqlite',
        'redis' => 'tcp',
        'rediss' => 'tls',
    ];

    /**
     * Parse the database configuration, hydrating options using a database configuration URL if possible.
	 * 如果可能的话,解析数据库配置,使用数据库配置URL
     *
     * @param  array|string  $config
     * @return array
     */
    public function parseConfiguration($config)
    {
        if (is_string($config)) {
            $config = ['url' => $config];
        }

        $url = Arr::pull($config, 'url');

        if (! $url) {
            return $config;
        }

        $rawComponents = $this->parseUrl($url);

        $decodedComponents = $this->parseStringsToNativeTypes(
            array_map('rawurldecode', $rawComponents)
        );

        return array_merge(
            $config,
            $this->getPrimaryOptions($decodedComponents),
            $this->getQueryOptions($rawComponents)
        );
    }

    /**
     * Get the primary database connection options.
	 * 获取主数据库连接选项
     *
     * @param  array  $url
     * @return array
     */
    protected function getPrimaryOptions($url)
    {
        return array_filter([
            'driver' => $this->getDriver($url),
            'database' => $this->getDatabase($url),
            'host' => $url['host'] ?? null,
            'port' => $url['port'] ?? null,
            'username' => $url['user'] ?? null,
            'password' => $url['pass'] ?? null,
        ], function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * Get the database driver from the URL.
	 * 从URL获取数据库驱动程序
     *
     * @param  array  $url
     * @return string|null
     */
    protected function getDriver($url)
    {
        $alias = $url['scheme'] ?? null;

        if (! $alias) {
            return;
        }

        return static::$driverAliases[$alias] ?? $alias;
    }

    /**
     * Get the database name from the URL.
	 * 从URL获取数据库名称
     *
     * @param  array  $url
     * @return string|null
     */
    protected function getDatabase($url)
    {
        $path = $url['path'] ?? null;

        return $path && $path !== '/' ? substr($path, 1) : null;
    }

    /**
     * Get all of the additional database options from the query string.
	 * 从查询字符串中获取所有其他数据库选项
     *
     * @param  array  $url
     * @return array
     */
    protected function getQueryOptions($url)
    {
        $queryString = $url['query'] ?? null;

        if (! $queryString) {
            return [];
        }

        $query = [];

        parse_str($queryString, $query);

        return $this->parseStringsToNativeTypes($query);
    }

    /**
     * Parse the string URL to an array of components.
	 * 将字符串URL解析为组件数组
     *
     * @param  string  $url
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseUrl($url)
    {
        $url = preg_replace('#^(sqlite3?):///#', '$1://null/', $url);

        $parsedUrl = parse_url($url);

        if ($parsedUrl === false) {
            throw new InvalidArgumentException('The database configuration URL is malformed.');
        }

        return $parsedUrl;
    }

    /**
     * Convert string casted values to their native types.
	 * 将字符串强制转换值转换为其本机类型
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function parseStringsToNativeTypes($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'parseStringsToNativeTypes'], $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        $parsedValue = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $parsedValue;
        }

        return $value;
    }

    /**
     * Get all of the current drivers aliases.
	 * 找出当前所有司机的化名
     *
     * @return array
     */
    public static function getDriverAliases()
    {
        return static::$driverAliases;
    }

    /**
     * Add the given driver alias to the driver aliases array.
	 * 将给定的驱动别名添加到驱动别名数组中
     *
     * @param  string  $alias
     * @param  string  $driver
     * @return void
     */
    public static function addDriverAlias($alias, $driver)
    {
        static::$driverAliases[$alias] = $driver;
    }
}
