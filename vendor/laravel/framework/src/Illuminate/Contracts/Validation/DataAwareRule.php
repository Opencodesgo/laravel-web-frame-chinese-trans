<?php
/**
 * Illuminate，契约，验证，数据感知规则
 */

namespace Illuminate\Contracts\Validation;

interface DataAwareRule
{
    /**
     * Set the data under validation.
	 * 设置正在验证的数据
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data);
}
