<?php
/**
 * Illuminate，文件系统，可锁定的文件
 */

namespace Illuminate\Filesystem;

use Exception;
use Illuminate\Contracts\Filesystem\LockTimeoutException;

class LockableFile
{
    /**
     * The file resource.
	 * 文件资源
     *
     * @var resource
     */
    protected $handle;

    /**
     * The file path.
	 * 文件路径
     *
     * @var string
     */
    protected $path;

    /**
     * Indicates if the file is locked.
	 * 文件是否被锁定
     *
     * @var bool
     */
    protected $isLocked = false;

    /**
     * Create a new File instance.
	 * 创建新文件实例
     *
     * @param  string  $path
     * @param  string  $mode
     * @return void
     */
    public function __construct($path, $mode)
    {
        $this->path = $path;

        $this->ensureDirectoryExists($path);
        $this->createResource($path, $mode);
    }

    /**
     * Create the file's directory if necessary.
	 * 如果需要，创建文件的目录。
     *
     * @param  string  $path
     * @return void
     */
    protected function ensureDirectoryExists($path)
    {
        if (! file_exists(dirname($path))) {
            @mkdir(dirname($path), 0777, true);
        }
    }

    /**
     * Create the file resource.
	 * 创建文件资源
     *
     * @param  string  $path
     * @param  string  $mode
     * @return void
     *
     * @throws \Exception
     */
    protected function createResource($path, $mode)
    {
        $this->handle = @fopen($path, $mode);

        if (! $this->handle) {
            throw new Exception('Unable to create lockable file: '.$path.'. Please ensure you have permission to create files in this location.');
        }
    }

    /**
     * Read the file contents.
	 * 读取文件内容
     *
     * @param  int|null  $length
     * @return string
     */
    public function read($length = null)
    {
        clearstatcache(true, $this->path);

        return fread($this->handle, $length ?? ($this->size() ?: 1));
    }

    /**
     * Get the file size.
	 * 得到文件大小
     *
     * @return int
     */
    public function size()
    {
        return filesize($this->path);
    }

    /**
     * Write to the file.
	 * 写入文件
     *
     * @param  string  $contents
     * @return $this
     */
    public function write($contents)
    {
        fwrite($this->handle, $contents);

        fflush($this->handle);

        return $this;
    }

    /**
     * Truncate the file.
	 * 清空文件
     *
     * @return $this
     */
    public function truncate()
    {
        rewind($this->handle);

        ftruncate($this->handle, 0);

        return $this;
    }

    /**
     * Get a shared lock on the file.
	 * 获取文件上的共享锁
     *
     * @param  bool  $block
     * @return $this
     *
     * @throws \Illuminate\Contracts\Filesystem\LockTimeoutException
     */
    public function getSharedLock($block = false)
    {
        if (! flock($this->handle, LOCK_SH | ($block ? 0 : LOCK_NB))) {
            throw new LockTimeoutException("Unable to acquire file lock at path [{$this->path}].");
        }

        $this->isLocked = true;

        return $this;
    }

    /**
     * Get an exclusive lock on the file.
	 * 对文件设置排他锁
     *
     * @param  bool  $block
     * @return bool
     *
     * @throws \Illuminate\Contracts\Filesystem\LockTimeoutException
     */
    public function getExclusiveLock($block = false)
    {
        if (! flock($this->handle, LOCK_EX | ($block ? 0 : LOCK_NB))) {
            throw new LockTimeoutException("Unable to acquire file lock at path [{$this->path}].");
        }

        $this->isLocked = true;

        return $this;
    }

    /**
     * Release the lock on the file.
	 * 释放文件锁
     *
     * @return $this
     */
    public function releaseLock()
    {
        flock($this->handle, LOCK_UN);

        $this->isLocked = false;

        return $this;
    }

    /**
     * Close the file.
	 * 关闭文件
     *
     * @return bool
     */
    public function close()
    {
        if ($this->isLocked) {
            $this->releaseLock();
        }

        return fclose($this->handle);
    }
}
