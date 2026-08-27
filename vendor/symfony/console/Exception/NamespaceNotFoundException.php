<?php
/**
 * Symfony，Component，Console，异常，未找到命名空间异常
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Exception;

/**
 * Represents an incorrect namespace typed in the console.
 * 表示在控制台中键入的不正确的名称空间。
 *
 * @author Pierre du Plessis <pdples@gmail.com>
 */
class NamespaceNotFoundException extends CommandNotFoundException
{
}
