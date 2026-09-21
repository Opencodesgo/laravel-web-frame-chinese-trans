<?php declare(strict_types=1);

/**
 * PHPUnit，框架，模拟对象，可验证的
 */

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\ExpectationFailedException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface Verifiable
{
    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
	 * 验证当前期望是否有效。如果一切正常，代码应直接返回；否则必须抛出异常。
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void;
}
