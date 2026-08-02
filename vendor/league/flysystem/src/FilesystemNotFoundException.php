<?php
/**
 * League，Flysystem，文件系统未发现异常
 */

namespace League\Flysystem;

use LogicException;

/**
 * Thrown when the MountManager cannot find a filesystem.
 * 当MountManager无法找到文件系统时抛出。
 */
class FilesystemNotFoundException extends LogicException implements FilesystemException
{
}
