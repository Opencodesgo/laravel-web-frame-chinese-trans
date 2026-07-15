<?php
/**
 * Doctrine，偏转器，单词偏转器
 */

declare(strict_types=1);

namespace Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word): string;
}
