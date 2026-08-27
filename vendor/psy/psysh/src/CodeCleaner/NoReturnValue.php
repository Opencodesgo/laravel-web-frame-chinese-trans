<?php
/**
 * Psy，代码清理，没有返回值
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

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;

/**
 * A class used internally by CodeCleaner to represent input, such as
 * non-expression statements, with no return value.
 * codeccleaner内部使用的一个类，用于表示输入，例如没有返回值的非表达式语句。
 *
 * Note that user code returning an instance of this class will act like it
 * has no return value, so you prolly shouldn't do that.
 */
class NoReturnValue
{
    /**
     * Get PhpParser AST expression for creating a new NoReturnValue.
	 * 获取PhpParser AST表达式来创建一个新的NoReturnValue。
     */
    public static function create(): New_
    {
        return new New_(new FullyQualifiedName(self::class));
    }
}
