<?php
/**
 * Symfony，Component，Finder，比较器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Comparator;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Comparator
{
    private $target;
    private $operator = '==';

    public function __construct(?string $target = null, string $operator = '==')
    {
        if (null === $target) {
            trigger_deprecation('symfony/finder', '5.4', 'Constructing a "%s" without setting "$target" is deprecated.', __CLASS__);
        }

        $this->target = $target;
        $this->doSetOperator($operator);
    }

    /**
     * Gets the target value.
	 * 获取目标值
     *
     * @return string
     */
    public function getTarget()
    {
        if (null === $this->target) {
            trigger_deprecation('symfony/finder', '5.4', 'Calling "%s" without initializing the target is deprecated.', __METHOD__);
        }

        return $this->target;
    }

    /**
     * @deprecated set the target via the constructor instead
     */
    public function setTarget(string $target)
    {
        trigger_deprecation('symfony/finder', '5.4', '"%s" is deprecated. Set the target via the constructor instead.', __METHOD__);

        $this->target = $target;
    }

    /**
     * Gets the comparison operator.
	 * 获取比较运算符
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Sets the comparison operator.
	 * 设置比较运算符
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated set the operator via the constructor instead
     */
    public function setOperator(string $operator)
    {
        trigger_deprecation('symfony/finder', '5.4', '"%s" is deprecated. Set the operator via the constructor instead.', __METHOD__);

        $this->doSetOperator('' === $operator ? '==' : $operator);
    }

    /**
     * Tests against the target.
	 * 针对目标进行测试
     *
     * @param mixed $test A test value
     *
     * @return bool
     */
    public function test($test)
    {
        if (null === $this->target) {
            trigger_deprecation('symfony/finder', '5.4', 'Calling "%s" without initializing the target is deprecated.', __METHOD__);
        }

        switch ($this->operator) {
            case '>':
                return $test > $this->target;
            case '>=':
                return $test >= $this->target;
            case '<':
                return $test < $this->target;
            case '<=':
                return $test <= $this->target;
            case '!=':
                return $test != $this->target;
        }

        return $test == $this->target;
    }

    private function doSetOperator(string $operator): void
    {
        if (!\in_array($operator, ['>', '<', '>=', '<=', '==', '!='])) {
            throw new \InvalidArgumentException(sprintf('Invalid operator "%s".', $operator));
        }

        $this->operator = $operator;
    }
}
