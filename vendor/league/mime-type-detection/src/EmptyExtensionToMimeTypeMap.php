<?php
/**
 * League，MimeTypeDetection，空的扩展名 Mime类型映射
 */

declare(strict_types=1);

namespace League\MimeTypeDetection;

class EmptyExtensionToMimeTypeMap implements ExtensionToMimeTypeMap
{
    public function lookupMimeType(string $extension): ?string
    {
        return null;
    }
}
