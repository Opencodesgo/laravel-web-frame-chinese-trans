<?php
/**
 * League，Flysystem，无效根异常
 */

namespace League\Flysystem;

use RuntimeException;

class InvalidRootException extends RuntimeException implements FilesystemException
{
}
