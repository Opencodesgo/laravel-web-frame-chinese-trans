<?php
/**
 * League，Flysystem，文件系统未发现异常
 */

namespace League\Flysystem;

use LogicException;

/**
 * Thrown when the MountManager cannot find a filesystem.
 */
class FilesystemNotFoundException extends LogicException
{
}
