<?php
/**
 * League，MimeTypeDetection，扩展查找
 */
 
declare(strict_types=1);

namespace League\MimeTypeDetection;

interface ExtensionLookup
{
    public function lookupExtension(string $mimetype): ?string;

    /**
     * @return string[]
     */
    public function lookupAllExtensions(string $mimetype): array;
}
