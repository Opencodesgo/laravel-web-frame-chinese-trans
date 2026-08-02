<?php
/**
 * League，Flysystem，不支持异常
 */

namespace League\Flysystem;

use RuntimeException;
use SplFileInfo;

class NotSupportedException extends RuntimeException implements FilesystemException
{
    /**
     * Create a new exception for a link.
	 * 为链接创建一个新的异常
     *
     * @param SplFileInfo $file
     *
     * @return static
     */
    public static function forLink(SplFileInfo $file)
    {
        $message = 'Links are not supported, encountered link at ';

        return new static($message . $file->getPathname());
    }

    /**
     * Create a new exception for a link.
	 * 为链接创建一个新的异常
     *
     * @param string $systemType
     *
     * @return static
     */
    public static function forFtpSystemType($systemType)
    {
        $message = "The FTP system type '$systemType' is currently not supported.";

        return new static($message);
    }
}
