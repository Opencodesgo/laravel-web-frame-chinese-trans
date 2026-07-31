<?php
/**
 * League，Flysystem，处理程序
 */

namespace League\Flysystem;

use BadMethodCallException;

/**
 * @deprecated
 */
abstract class Handler
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * Constructor.
	 * 构造方法
     *
     * @param FilesystemInterface $filesystem
     * @param string              $path
     */
    public function __construct(FilesystemInterface $filesystem = null, $path = null)
    {
        $this->path = $path;
        $this->filesystem = $filesystem;
    }

    /**
     * Check whether the entree is a directory.
	 * 检查entree是否是一个目录
     *
     * @return bool
     */
    public function isDir()
    {
        return $this->getType() === 'dir';
    }

    /**
     * Check whether the entree is a file.
	 * 检查主菜是否有文件
     *
     * @return bool
     */
    public function isFile()
    {
        return $this->getType() === 'file';
    }

    /**
     * Retrieve the entree type (file|dir).
	 * 检索entree类型(file | dir)
     *
     * @return string file or dir
     */
    public function getType()
    {
        $metadata = $this->filesystem->getMetadata($this->path);

        return $metadata ? $metadata['type'] : 'dir';
    }

    /**
     * Set the Filesystem object.
	 * 设置文件系统对象
     *
     * @param FilesystemInterface $filesystem
     *
     * @return $this
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }
    
    /**
     * Retrieve the Filesystem object.
	 * 检索文件系统对象
     *
     * @return FilesystemInterface
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Set the entree path.
	 * 设置主菜路径
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Retrieve the entree path.
	 * 找回主菜的路
     *
     * @return string path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Plugins pass-through.
	 * 插件通过
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        array_unshift($arguments, $this->path);
        $callback = [$this->filesystem, $method];

        try {
            return call_user_func_array($callback, $arguments);
        } catch (BadMethodCallException $e) {
            throw new BadMethodCallException(
                'Call to undefined method '
                . get_called_class()
                . '::' . $method
            );
        }
    }
}
