<?php
/**
 * Spatie，Backtrace，代码片段，片段提供者
 */

namespace Spatie\Backtrace\CodeSnippets;

interface SnippetProvider
{
    public function numberOfLines(): int;

    public function getLine(?int $lineNumber = null): string;

    public function getNextLine(): string;
}
