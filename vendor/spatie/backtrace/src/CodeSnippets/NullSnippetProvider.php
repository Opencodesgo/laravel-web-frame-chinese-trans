<?php
/**
 * Spatie，Backtrace，代码片段，空代码段提供程序
 */

namespace Spatie\Backtrace\CodeSnippets;

class NullSnippetProvider implements SnippetProvider
{
    public function numberOfLines(): int
    {
        return 1;
    }

    public function getLine(?int $lineNumber = null): string
    {
        return $this->getNextLine();
    }

    public function getNextLine(): string
    {
        return "File not found for code snippet";
    }
}
