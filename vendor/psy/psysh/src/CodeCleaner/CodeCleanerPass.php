<?php
/**
 * Psy，代码清洁，代码清理员通行证
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

use PhpParser\NodeVisitorAbstract;

/**
 * A CodeCleaner pass is a PhpParser Node Visitor.
 * 一个CodeCleaner传递是一个PhpParser节点访问者。
 */
abstract class CodeCleanerPass extends NodeVisitorAbstract
{
    // Wheee!
}
