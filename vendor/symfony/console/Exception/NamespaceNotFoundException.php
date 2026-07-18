<?php
/**
 * Symfony，组件，控制台，异常，命名空间未发现异常
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
 *
 * @author Pierre du Plessis <pdples@gmail.com>
 */
class NamespaceNotFoundException extends CommandNotFoundException
{
}
