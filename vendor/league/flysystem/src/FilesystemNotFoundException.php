<?php
/**
 * League，Flysystem，Filesystem 未找到异常
 */

namespace League\Flysystem;

use LogicException;

/**
 * Thrown when the MountManager cannot find a filesystem.
 * 当 MountManager 找不到文件系统时抛出
 */
class FilesystemNotFoundException extends LogicException implements FilesystemException
{
}
