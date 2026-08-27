<?php
/**
 * League，Flysystem，不支持校验和算法
 */
 
declare(strict_types=1);

namespace League\Flysystem;

use InvalidArgumentException;

final class ChecksumAlgoIsNotSupported extends InvalidArgumentException
{

}
