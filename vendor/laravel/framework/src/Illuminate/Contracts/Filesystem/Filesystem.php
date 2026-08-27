<?php
/**
 * Illuminate，契约，文件系统，文件系统接口
 */

namespace Illuminate\Contracts\Filesystem;

interface Filesystem
{
    /**
     * The public visibility setting.
	 * 公共能见度设置
     *
     * @var string
     */
    const VISIBILITY_PUBLIC = 'public';

    /**
     * The private visibility setting.
	 * 私有可见性设置
     *
     * @var string
     */
    const VISIBILITY_PRIVATE = 'private';

    /**
     * Determine if a file exists.
	 * 确定文件是否存在
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path);

    /**
     * Get the contents of a file.
	 * 得到文件内容
     *
     * @param  string  $path
     * @return string|null
     */
    public function get($path);

    /**
     * Get a resource to read the file.
	 * 获取读取文件的资源
     *
     * @param  string  $path
     * @return resource|null The path resource or null on failure.
     */
    public function readStream($path);

    /**
     * Write the contents of a file.
	 * 写入文件的内容
     *
     * @param  string  $path
     * @param  string|resource  $contents
     * @param  mixed  $options
     * @return bool
     */
    public function put($path, $contents, $options = []);

    /**
     * Write a new file using a stream.
	 * 使用流写一个新文件
     *
     * @param  string  $path
     * @param  resource  $resource
     * @param  array  $options
     * @return bool
     */
    public function writeStream($path, $resource, array $options = []);

    /**
     * Get the visibility for the given path.
	 * 获取给定路径的可见性
     *
     * @param  string  $path
     * @return string
     */
    public function getVisibility($path);

    /**
     * Set the visibility for the given path.
	 * 设置给定路径的可见性
     *
     * @param  string  $path
     * @param  string  $visibility
     * @return bool
     */
    public function setVisibility($path, $visibility);

    /**
     * Prepend to a file.
	 * 添加到文件中
     *
     * @param  string  $path
     * @param  string  $data
     * @return bool
     */
    public function prepend($path, $data);

    /**
     * Append to a file.
	 * 附加行到一个文件
     *
     * @param  string  $path
     * @param  string  $data
     * @return bool
     */
    public function append($path, $data);

    /**
     * Delete the file at a given path.
	 * 删除指定路径下的文件
     *
     * @param  string|array  $paths
     * @return bool
     */
    public function delete($paths);

    /**
     * Copy a file to a new location.
	 * 将文件复制到新位置
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function copy($from, $to);

    /**
     * Move a file to a new location.
	 * 将文件移动到新位置
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function move($from, $to);

    /**
     * Get the file size of a given file.
	 * 获取给定文件的文件大小
     *
     * @param  string  $path
     * @return int
     */
    public function size($path);

    /**
     * Get the file's last modification time.
	 * 获取文件的最后修改时间
     *
     * @param  string  $path
     * @return int
     */
    public function lastModified($path);

    /**
     * Get an array of all files in a directory.
	 * 获取目录中所有文件的数组
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function files($directory = null, $recursive = false);

    /**
     * Get all of the files from the given directory (recursive).
	 * 从给定目录（递归）获取所有文件
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allFiles($directory = null);

    /**
     * Get all of the directories within a given directory.
	 * 获取给定目录中的所有目录
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function directories($directory = null, $recursive = false);

    /**
     * Get all (recursive) of the directories within a given directory.
	 * 获取给定目录中的所有（递归）目录
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allDirectories($directory = null);

    /**
     * Create a directory.
	 * 创建目录
     *
     * @param  string  $path
     * @return bool
     */
    public function makeDirectory($path);

    /**
     * Recursively delete a directory.
	 * 递归删除目录
     *
     * @param  string  $directory
     * @return bool
     */
    public function deleteDirectory($directory);
}
