<?php
/**
 * Illuminate，契约，文件系统，云接口
 */

namespace Illuminate\Contracts\Filesystem;

interface Cloud extends Filesystem
{
    /**
     * Get the URL for the file at the given path.
	 * 获取给定路径下文件的URL
     *
     * @param  string  $path
     * @return string
     */
    public function url($path);
}
