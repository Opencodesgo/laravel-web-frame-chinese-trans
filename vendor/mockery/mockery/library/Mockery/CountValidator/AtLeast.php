<?php
/**
 * Mockery，计数验证器，至少
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\CountValidator;

use Mockery\Exception\InvalidCountException;

use const PHP_EOL;

class AtLeast extends CountValidatorAbstract
{
    /**
     * Checks if the validator can accept an additional nth call
	 * 检查验证器是否可以接受额外的第n个调用
     *
     * @param int $n
     *
     * @return bool
     */
    public function isEligible($n)
    {
        return true;
    }

    /**
     * Validate the call count against this validator
	 * 根据此验证器验证调用计数
     *
     * @param int $n
     *
     * @throws InvalidCountException
     * @return bool
     */
    public function validate($n)
    {
        if ($this->_limit > $n) {
            $exception = new InvalidCountException(
                'Method ' . (string) $this->_expectation
                . ' from ' . $this->_expectation->getMock()->mockery_getName()
                . ' should be called' . PHP_EOL
                . ' at least ' . $this->_limit . ' times but called ' . $n
                . ' times.'
            );

            $exception->setMock($this->_expectation->getMock())
                ->setMethodName((string) $this->_expectation)
                ->setExpectedCountComparative('>=')
                ->setExpectedCount($this->_limit)
                ->setActualCount($n);
            throw $exception;
        }
    }
}
