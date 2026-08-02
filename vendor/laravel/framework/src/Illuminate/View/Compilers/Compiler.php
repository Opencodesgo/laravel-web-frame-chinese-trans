<?php
/**
 * Illuminate，视图，编译，编译器
 */

namespace Illuminate\View\Compilers;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

abstract class Compiler
{
    /**
     * The Filesystem instance.
	 * 文件系统实例
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Get the cache path for the compiled views.
	 * 获取编译视图的缓存路径
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Create a new compiler instance.
	 * 创建新的编译器实例
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $cachePath
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Filesystem $files, $cachePath)
    {
        if (! $cachePath) {
            throw new InvalidArgumentException('Please provide a valid cache path.');
        }

        $this->files = $files;
        $this->cachePath = $cachePath;
    }

    /**
     * Get the path to the compiled version of a view.
	 * 获取视图的编译版本的路径
     *
     * @param  string  $path
     * @return string
     */
    public function getCompiledPath($path)
    {
        return $this->cachePath.'/'.sha1('v2'.$path).'.php';
    }

    /**
     * Determine if the view at the given path is expired.
	 * 确定给定路径上的视图是否已过期
     *
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path)
    {
        $compiled = $this->getCompiledPath($path);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
		// 如果编译的文件不存在，我们将指出视图已过期。
        if (! $this->files->exists($compiled)) {
            return true;
        }

        return $this->files->lastModified($path) >=
               $this->files->lastModified($compiled);
    }

    /**
     * Create the compiled file directory if necessary.
	 * 如果需要，创建编译后的文件目录。
     *
     * @param  string  $path
     * @return void
     */
    protected function ensureCompiledDirectoryExists($path)
    {
        if (! $this->files->exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }
}
