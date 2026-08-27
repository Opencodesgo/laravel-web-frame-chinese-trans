<?php
/**
 * Carbon，Doctrine，Carbon 不可变类型
 */

declare(strict_types=1);

namespace Carbon\Doctrine;

class CarbonImmutableType extends DateTimeImmutableType implements CarbonDoctrineType
{
}
