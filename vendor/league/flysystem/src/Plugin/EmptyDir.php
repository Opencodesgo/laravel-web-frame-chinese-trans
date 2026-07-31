<?php
/**
 * League，Flysystem，Plugin，空目录
 */

namespace League\Flysystem\Plugin;

class EmptyDir extends AbstractPlugin
{
    /**
     * Get the method name.
	 * 获取方法名称
     *
     * @return string
     */
    public function getMethod()
    {
        return 'emptyDir';
    }

    /**
     * Empty a directory's contents.
	 * 清空目录的内容
     *
     * @param string $dirname
     */
    public function handle($dirname)
    {
        $listing = $this->filesystem->listContents($dirname, false);

        foreach ($listing as $item) {
            if ($item['type'] === 'dir') {
                $this->filesystem->deleteDir($item['path']);
            } else {
                $this->filesystem->delete($item['path']);
            }
        }
    }
}
