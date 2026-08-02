<?php
/**
 * League，Flysystem，连接运行时异常
 */

namespace League\Flysystem;

use RuntimeException;

class ConnectionRuntimeException extends RuntimeException implements FilesystemException
{
}
