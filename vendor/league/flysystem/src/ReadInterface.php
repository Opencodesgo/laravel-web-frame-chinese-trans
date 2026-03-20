<?php
/**
 * League，Flysystem，读取接口
 */

namespace League\Flysystem;

interface ReadInterface
{
    /**
     * Check whether a file exists.
	 * 检查文件是否存在
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path);

    /**
     * Read a file.
	 * 读取文件
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path);

    /**
     * Read a file as a stream.
	 * 读取文件为流
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path);

    /**
     * List contents of a directory.
	 * 列表目录的内容
     *
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false);

    /**
     * Get all the meta data of a file or directory.
	 * 获取文件或目录的所有元数据
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path);

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path);

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path);

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path);

    /**
     * Get the visibility of a file.
	 * 获得文件的可见性
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path);
}
