<?php
/**
 * Symfony，Component，Console，助手，表式分离器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper;

/**
 * Marks a row as being a separator.
 * 标记一行为分隔符
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TableSeparator extends TableCell
{
    public function __construct(array $options = [])
    {
        parent::__construct('', $options);
    }
}
