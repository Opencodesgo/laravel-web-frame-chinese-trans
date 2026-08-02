<?php
/**
 * League，MimeTypeDetection，扩展到 Mime 类型映射
 */

declare(strict_types=1);

namespace League\MimeTypeDetection;

interface ExtensionToMimeTypeMap
{
    public function lookupMimeType(string $extension): ?string;
}
