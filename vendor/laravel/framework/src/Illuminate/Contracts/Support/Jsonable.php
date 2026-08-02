<?php
/**
 * Illuminate，契约，支持，Json
 */

namespace Illuminate\Contracts\Support;

interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
	 * 转换对象为其JSON表示形式
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}
