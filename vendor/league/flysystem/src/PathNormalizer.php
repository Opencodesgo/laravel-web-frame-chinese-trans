<?php
/**
 * League，Flysystem，路径标准化者
 */

declare(strict_types=1);

namespace League\Flysystem;

interface PathNormalizer
{
    public function normalizePath(string $path): string;
}
