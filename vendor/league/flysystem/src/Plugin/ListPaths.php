<?php
/**
 * League，Flysystem，Plugin，列表路径
 */

namespace League\Flysystem\Plugin;

class ListPaths extends AbstractPlugin
{
    /**
     * Get the method name.
	 * 得到方法名
     *
     * @return string
     */
    public function getMethod()
    {
        return 'listPaths';
    }

    /**
     * List all paths.
	 * 列出所有路径
     *
     * @param string $directory
     * @param bool   $recursive
     *
     * @return string[] paths
     */
    public function handle($directory = '', $recursive = false)
    {
        $result = [];
        $contents = $this->filesystem->listContents($directory, $recursive);

        foreach ($contents as $object) {
            $result[] = $object['path'];
        }

        return $result;
    }
}
