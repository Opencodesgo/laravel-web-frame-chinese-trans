<?php
/**
 * Illuminate，契约，数据库，Eloquent，可塑的
 */

namespace Illuminate\Contracts\Database\Eloquent;

interface Castable
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
	 * 获取从/到此施法目标施法时使用的施法者类的名称
     *
     * @return string|\Illuminate\Contracts\Database\Eloquent\CastsAttributes|\Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes
     */
    public static function castUsing();
}
