<?php
/**
 * League，Flysystem，无法检查目录是否存在
 */

declare(strict_types=1);

namespace League\Flysystem;

class UnableToCheckDirectoryExistence extends UnableToCheckExistence
{
    public function operation(): string
    {
        return FilesystemOperationFailed::OPERATION_DIRECTORY_EXISTS;
    }
}
