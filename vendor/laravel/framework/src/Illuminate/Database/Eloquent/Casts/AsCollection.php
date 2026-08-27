<?php
/**
 * Illuminate，数据库，Eloquent，生效，做为集合
 */

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;

class AsCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
	 * 当从/到这个施法目标施法时使用施法者职业
     *
     * @param  array  $arguments
     * @return CastsAttributes<\Illuminate\Support\Collection<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                if (! isset($attributes[$key])) {
                    return;
                }

                $data = json_decode($attributes[$key], true);

                return is_array($data) ? new Collection($data) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [$key => json_encode($value)];
            }
        };
    }
}
