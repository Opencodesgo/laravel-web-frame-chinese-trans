<?php
/**
 * Mockery，计数验证器，计数验证器抽象
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\CountValidator;

use Mockery\Expectation;

abstract class CountValidatorAbstract implements CountValidatorInterface
{
    /**
     * Expectation for which this validator is assigned
	 * 该验证器被分配的期望
     *
     * @var Expectation
     */
    protected $_expectation = null;

    /**
     * Call count limit
	 * 呼叫计数极限
     *
     * @var int
     */
    protected $_limit = null;

    /**
     * Set Expectation object and upper call limit
	 * 设置期望对象和上呼叫限制
     *
     * @param int $limit
     */
    public function __construct(Expectation $expectation, $limit)
    {
        $this->_expectation = $expectation;
        $this->_limit = $limit;
    }

    /**
     * Checks if the validator can accept an additional nth call
	 * 检查验证器是否接受额外的nth调用
     *
     * @param int $n
     *
     * @return bool
     */
    public function isEligible($n)
    {
        return $n < $this->_limit;
    }

    /**
     * Validate the call count against this validator
	 * 通过该验证器验证调用计数
     *
     * @param int $n
     *
     * @return bool
     */
    abstract public function validate($n);
}
