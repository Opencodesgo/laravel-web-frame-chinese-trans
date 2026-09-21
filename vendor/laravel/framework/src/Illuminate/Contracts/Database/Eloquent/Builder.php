<?php
/**
 * Illuminate，契约，数据库，Eloquent，生成器
 */

namespace Illuminate\Contracts\Database\Eloquent;

use Illuminate\Contracts\Database\Query\Builder as BaseContract;

/**
 * This interface is intentionally empty and exists to improve IDE support.
 * 此接口故意为空，其存在是为了改进IDE支持。
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
interface Builder extends BaseContract
{
}
