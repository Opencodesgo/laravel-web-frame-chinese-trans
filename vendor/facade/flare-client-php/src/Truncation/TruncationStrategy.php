<?php
/**
 * Facade，Flare Client，截断，报告修整器
 */

namespace Facade\FlareClient\Truncation;

interface TruncationStrategy
{
    public function execute(array $payload): array;
}
