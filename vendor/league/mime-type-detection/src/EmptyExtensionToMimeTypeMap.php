<?php
/**
 * League，MimeTypeDetection，Mime 类型映射的空扩展名
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
