<?php
/**
 * League，Flysystem，Plugin，列表文件
 */

namespace League\Flysystem\Plugin;

class ListFiles extends AbstractPlugin
{
    /**
     * Get the method name.
	 * 得到方法名
     *
     * @return string
     */
    public function getMethod()
    {
        return 'listFiles';
    }

    /**
     * List all files in the directory.
	 * 列出目录中的所有文件
     *
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     */
    public function handle($directory = '', $recursive = false)
    {
        $contents = $this->filesystem->listContents($directory, $recursive);

        $filter = function ($object) {
            return $object['type'] === 'file';
        };

        return array_values(array_filter($contents, $filter));
    }
}
