<?php
/**
 * League，Mime类型检测，覆盖扩展到Mime类型映射
 */

namespace League\MimeTypeDetection;

class OverridingExtensionToMimeTypeMap implements ExtensionToMimeTypeMap
{
    /**
     * @var ExtensionToMimeTypeMap
     */
    private $innerMap;

    /**
     * @var string[]
     */
    private $overrides;

    /**
     * @param array<string, string>  $overrides
     */
    public function __construct(ExtensionToMimeTypeMap $innerMap, array $overrides)
    {
        $this->innerMap = $innerMap;
        $this->overrides = $overrides;
    }

    public function lookupMimeType(string $extension): ?string
    {
        return $this->overrides[$extension] ?? $this->innerMap->lookupMimeType($extension);
    }
}
