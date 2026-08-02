<?php
/**
 * Mockery，计数验证器，计数验证器接口
 */

namespace Mockery\CountValidator;

interface CountValidatorInterface
{
    /**
     * Checks if the validator can accept an additional nth call
	 * 检查验证器是否可以接受额外的第n个调
     *
     * @param int $n
     *
     * @return bool
     */
    public function isEligible($n);

    /**
     * Validate the call count against this validator
	 * 根据此验证器验证调用计数
     *
     * @param int $n
     *
     * @return bool
     */
    public function validate($n);
}
