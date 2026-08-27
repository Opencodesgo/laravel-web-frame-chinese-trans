<?php
/**
 * Illuminate，数据库，Eloquent，属性缺失异常
 */

namespace Illuminate\Database\Eloquent;

use OutOfBoundsException;

class MissingAttributeException extends OutOfBoundsException
{
    /**
     * Create a new missing attribute exception instance.
	 * 创建一个新的缺失属性异常实例
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @return void
     */
    public function __construct($model, $key)
    {
        parent::__construct(sprintf(
            'The attribute [%s] either does not exist or was not retrieved for model [%s].',
            $key, get_class($model)
        ));
    }
}
