<?php
/**
 * Psy，代码清理，离开 Psysh AlonePass
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\CodeCleaner;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Psy\Exception\RuntimeException;

/**
 * Validate that the user input does not reference the `$__psysh__` variable.
 * 验证用户输入没有引用‘ $__psysh__ ’变量。
 */
class LeavePsyshAlonePass extends CodeCleanerPass
{
    /**
     * Validate that the user input does not reference the `$__psysh__` variable.
	 * 验证用户输入没有引用‘ $__psysh__ ’变量。
     *
     * @throws RuntimeException if the user is messing with $__psysh__
     *
     * @param Node $node
     *
     * @return int|Node|null Replacement node (or special return value)
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Variable && $node->name === '__psysh__') {
            throw new RuntimeException('Don\'t mess with $__psysh__; bad things will happen');
        }
    }
}
