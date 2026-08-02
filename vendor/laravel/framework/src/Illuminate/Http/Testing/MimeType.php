<?php
/**
 * Illuminate，Http，测试，文档类型
 */

namespace Illuminate\Http\Testing;

use Illuminate\Support\Arr;
use Symfony\Component\Mime\MimeTypes;

class MimeType
{
    /**
     * The mime types instance.
	 * mime类型实例
     *
     * @var \Symfony\Component\Mime\MimeTypes|null
     */
    private static $mime;

    /**
     * Get the mime types instance.
	 * 获取mime类型实例
     *
     * @return \Symfony\Component\Mime\MimeTypesInterface
     */
    public static function getMimeTypes()
    {
        if (self::$mime === null) {
            self::$mime = new MimeTypes();
        }

        return self::$mime;
    }

    /**
     * Get the MIME type for a file based on the file's extension.
	 * 根据文件的扩展名获取文件的MIME类型
     *
     * @param  string  $filename
     * @return string
     */
    public static function from($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return self::get($extension);
    }

    /**
     * Get the MIME type for a given extension or return all mimes.
	 * 获取给定扩展的MIME类型或返回所有MIME
     *
     * @param  string  $extension
     * @return string
     */
    public static function get($extension)
    {
        return Arr::first(self::getMimeTypes()->getMimeTypes($extension)) ?? 'application/octet-stream';
    }

    /**
     * Search for the extension of a given MIME type.
	 * 搜索给定MIME类型的扩展名
     *
     * @param  string  $mimeType
     * @return string|null
     */
    public static function search($mimeType)
    {
        return Arr::first(self::getMimeTypes()->getExtensions($mimeType));
    }
}
