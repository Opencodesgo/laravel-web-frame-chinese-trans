<?php
/**
 * Symfony，Component，Console，完成，输出，完成输出接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Completion\Output;

use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Transforms the {@see CompletionSuggestions} object into output readable by the shell completion.
 * 将{ @see completionunk }对象转换为通过shell完成可读的输出。
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
interface CompletionOutputInterface
{
    public function write(CompletionSuggestions $suggestions, OutputInterface $output): void;
}
