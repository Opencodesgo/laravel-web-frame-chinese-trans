<?php
/**
 * League，Flysystem，连接错误异常
 */

namespace League\Flysystem;

use ErrorException;

class ConnectionErrorException extends ErrorException implements FilesystemException
{
}
