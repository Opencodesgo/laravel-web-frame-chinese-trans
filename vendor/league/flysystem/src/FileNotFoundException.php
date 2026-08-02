<?php
/**
 * League，Flysystem，文件未发现异常
 */

namespace League\Flysystem;

use Exception as BaseException;

class FileNotFoundException extends Exception
{
    /**
     * @var string
     */
    protected $path;

    /**
     * Constructor.
	 * 构造方法
     *
     * @param string     $path
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($path, $code = 0, BaseException $previous = null)
    {
        $this->path = $path;

        parent::__construct('File not found at path: ' . $this->getPath(), $code, $previous);
    }

    /**
     * Get the path which was not found.
	 * 获取未找到的路径
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
