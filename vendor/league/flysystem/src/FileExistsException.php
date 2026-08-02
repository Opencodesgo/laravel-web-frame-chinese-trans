<?php
/**
 * League，Flysystem，文件存在异常
 */

namespace League\Flysystem;

use Exception as BaseException;

class FileExistsException extends Exception
{
    /**
     * @var string
     */
    protected $path;

    /**
     * Constructor.
	 * 构造方法
     *
     * @param string        $path
     * @param int           $code
     * @param BaseException $previous
     */
    public function __construct($path, $code = 0, BaseException $previous = null)
    {
        $this->path = $path;

        parent::__construct('File already exists at path: ' . $this->getPath(), $code, $previous);
    }

    /**
     * Get the path which was found.
	 * 获取找到的路径
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
