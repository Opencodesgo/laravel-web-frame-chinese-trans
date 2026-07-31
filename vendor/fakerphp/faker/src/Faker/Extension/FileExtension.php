<?php
/**
 * Faker，扩展，文件扩展
 */

namespace Faker\Extension;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 */
interface FileExtension extends Extension
{
    /**
     * Get a random MIME type
	 * 随机MIME类型
     *
     * @example 'video/avi'
     */
    public function mimeType(): string;

    /**
     * Get a random file extension (without a dot)
	 * 得到一个随机的文件扩展(没有一个点)
     *
     * @example avi
     */
    public function extension(): string;

    /**
     * Get a full path to a new real file on the system.
	 * 在系统上得到一个新的真实文件的完整路径
     */
    public function filePath(): string;
}
