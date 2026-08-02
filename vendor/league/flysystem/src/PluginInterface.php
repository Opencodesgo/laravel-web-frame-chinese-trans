<?php
/**
 * League，Flysystem，插件接口
 */

namespace League\Flysystem;

interface PluginInterface
{
    /**
     * Get the method name.
	 * 得到方法名称
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set the Filesystem object.
	 * 设置 Filesystem 对象
     *
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem);
}
