<?php
/**
 * League，Flysystem，无法检查文件是否存在
 */

declare(strict_types=1);

namespace League\Flysystem;

class UnableToCheckFileExistence extends UnableToCheckExistence
{
    public function operation(): string
    {
        return FilesystemOperationFailed::OPERATION_FILE_EXISTS;
    }
}
