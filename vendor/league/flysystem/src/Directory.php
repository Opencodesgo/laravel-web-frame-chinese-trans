<?php
/**
 * League，Flysystem，目录
 */

namespace League\Flysystem;

/**
 * @deprecated
 */
class Directory extends Handler
{
    /**
     * Delete the directory.
	 * 删除目录
     *
     * @return bool
     */
    public function delete()
    {
        return $this->filesystem->deleteDir($this->path);
    }

    /**
     * List the directory contents.
	 * 列出目录内容
     *
     * @param bool $recursive
     *
     * @return array|bool directory contents or false
     */
    public function getContents($recursive = false)
    {
        return $this->filesystem->listContents($this->path, $recursive);
    }
}
