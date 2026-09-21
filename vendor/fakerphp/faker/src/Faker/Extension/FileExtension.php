<?php
/**
 * Faker，扩展，文件扩展名
 */

namespace Faker\Extension;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 */
interface FileExtension extends Extension
{
    /**
     * Get a random MIME type
	 * 获取一个随机的MIME类型
     *
     * @example 'video/avi'
     */
    public function mimeType(): string;

    /**
     * Get a random file extension (without a dot)
	 * 获取随机文件扩展名（不带点）
     *
     * @example avi
     */
    public function extension(): string;

    /**
     * Get a full path to a new real file on the system.
	 * 获取系统上新文件的完整路径
     */
    public function filePath(): string;
}
