<?php
/**
 * League，Flysystem，Adapter，Polyfill，流式写特性
 */

namespace League\Flysystem\Adapter\Polyfill;

use League\Flysystem\Config;
use League\Flysystem\Util;

trait StreamedWritingTrait
{
    /**
     * Stream fallback delegator.
	 * 流回退委托
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     * @param string   $fallback
     *
     * @return mixed fallback result
     */
    protected function stream($path, $resource, Config $config, $fallback)
    {
        Util::rewindStream($resource);
        $contents = stream_get_contents($resource);
        $fallbackCall = [$this, $fallback];

        return call_user_func($fallbackCall, $path, $contents, $config);
    }

    /**
     * Write using a stream.
	 * 使用流写入
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config
     *
     * @return mixed false or file metadata
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->stream($path, $resource, $config, 'write');
    }

    /**
     * Update a file using a stream.
	 * 使用流更新文件
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object or visibility setting
     *
     * @return mixed false of file metadata
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->stream($path, $resource, $config, 'update');
    }

    // Required abstract methods
	// 包含抽象方法
    abstract public function write($pash, $contents, Config $config);
    abstract public function update($pash, $contents, Config $config);
}
