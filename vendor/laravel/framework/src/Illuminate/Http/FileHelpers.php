<?php
/**
 * Illuminate，Http，文件助手
 */

namespace Illuminate\Http;

use Illuminate\Support\Str;

trait FileHelpers
{
    /**
     * The cache copy of the file's hash name.
	 * 文件哈希的缓存副本
     *
     * @var string
     */
    protected $hashName = null;

    /**
     * Get the fully qualified path to the file.
	 * 获取文件的完全限定路径
     *
     * @return string
     */
    public function path()
    {
		// 通过SymfonyFile，调取的是SplFileInfo
        return $this->getRealPath();
    }

    /**
     * Get the file's extension.
	 * 获取文件扩展名
     *
     * @return string
     */
    public function extension()
    {
		// / SymfonyFile里
        return $this->guessExtension();
    }

    /**
     * Get a filename for the file.
	 * 得到文件的文件名
     *
     * @param  string|null  $path
     * @return string
     */
    public function hashName($path = null)
    {
        if ($path) {
            $path = rtrim($path, '/').'/';
        }

        $hash = $this->hashName ?: $this->hashName = Str::random(40);

        if ($extension = $this->guessExtension()) {
            $extension = '.'.$extension;
        }

        return $path.$hash.$extension;
    }
}
