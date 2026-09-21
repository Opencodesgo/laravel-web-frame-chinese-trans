<?php
/**
 * Illuminate，契约，数据库，Eloquent，可铸的
 */

namespace Illuminate\Contracts\Database\Eloquent;

interface Castable
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
	 * 获取从/到此施法目标施法时使用的施法者类的名称
     *
     * @param  array  $arguments
     * @return class-string<CastsAttributes|CastsInboundAttributes>|CastsAttributes|CastsInboundAttributes
     */
    public static function castUsing(array $arguments);
}
