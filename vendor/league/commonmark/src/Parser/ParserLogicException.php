<?php
/**
 * League，CommonMark，解析器，解析器逻辑异常
 */

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Parser;

use League\CommonMark\Exception\CommonMarkException;

class ParserLogicException extends \LogicException implements CommonMarkException
{
}
