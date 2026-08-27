<?php
/**
 * Symfony，Component，Uid，基于时间的Uid接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Uid;

/**
 * Interface to describe UIDs that contain a DateTimeImmutable as part of their behaviour.
 * 接口来描述包含DateTimeImmutable作为其行为一部分的uid。
 *
 * @author Barney Hanlon <barney.hanlon@cushon.co.uk>
 */
interface TimeBasedUidInterface
{
    public function getDateTime(): \DateTimeImmutable;
}
