<?php
/**
 * Symfony，Component，HttpFoundation，文件，流
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\File;

/**
 * A PHP stream of unknown size.
 * 大小未知的PHP流。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Stream extends File
{
    public function getSize(): int|false
    {
        return false;
    }
}
