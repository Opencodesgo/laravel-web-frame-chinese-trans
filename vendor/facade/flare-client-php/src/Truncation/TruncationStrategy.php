<?php
/**
 * Facade，FlareClient，截断，截断策略
 */

namespace Facade\FlareClient\Truncation;

interface TruncationStrategy
{
    public function execute(array $payload): array;
}
