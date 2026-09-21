<?php
/**
 * Illuminate，契约，验证，不妥协的验证器
 */

namespace Illuminate\Contracts\Validation;

interface UncompromisedVerifier
{
    /**
     * Verify that the given data has not been compromised in data leaks.
	 * 验证给定的数据没有在数据泄漏中受到损害
     *
     * @param  array  $data
     * @return bool
     */
    public function verify($data);
}
