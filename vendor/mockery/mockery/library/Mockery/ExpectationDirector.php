<?php
/**
 * Mockery，期待主管
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery;

use Mockery;
use Mockery\Exception\NoMatchingExpectationException;

use function array_pop;
use function array_unshift;
use function end;

use const PHP_EOL;

class ExpectationDirector
{
    /**
     * Stores an array of all default expectations for this mock
	 * 为这个模拟存储一系列默认的期望
     *
     * @var list<ExpectationInterface>
     */
    protected $_defaults = [];

    /**
     * Stores an array of all expectations for this mock
	 * 为这个模拟存储一系列期望
     *
     * @var list<ExpectationInterface>
     */
    protected $_expectations = [];

    /**
     * The expected order of next call
	 * 下一次呼叫的预期顺序
     *
     * @var int
     */
    protected $_expectedOrder = null;

    /**
     * Mock object the director is attached to
	 * 指示器附加到的模拟对象
     *
     * @var LegacyMockInterface|MockInterface
     */
    protected $_mock = null;

    /**
     * Method name the director is directing
	 * 方法名称:导演是导演
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Constructor
	 * 构造方法
     *
     * @param string $name
     */
    public function __construct($name, LegacyMockInterface $mock)
    {
        $this->_name = $name;
        $this->_mock = $mock;
    }

    /**
     * Add a new expectation to the director
	 * 向导演增加一个新的期望
     */
    public function addExpectation(Expectation $expectation)
    {
        $this->_expectations[] = $expectation;
    }

    /**
     * Handle a method call being directed by this instance
	 * 处理由此实例引导的方法调用
     *
     * @return mixed
     */
    public function call(array $args)
    {
        $expectation = $this->findExpectation($args);
        if ($expectation !== null) {
            return $expectation->verifyCall($args);
        }

        $exception = new NoMatchingExpectationException(
            'No matching handler found for '
            . $this->_mock->mockery_getName() . '::'
            . Mockery::formatArgs($this->_name, $args)
            . '. Either the method was unexpected or its arguments matched'
            . ' no expected argument list for this method'
            . PHP_EOL . PHP_EOL
            . Mockery::formatObjects($args)
        );

        $exception->setMock($this->_mock)
            ->setMethodName($this->_name)
            ->setActualArguments($args);

        throw $exception;
    }

    /**
     * Attempt to locate an expectation matching the provided args
	 * 尝试定位一个期望匹配提供的args
     *
     * @return mixed
     */
    public function findExpectation(array $args)
    {
        $expectation = null;

        if ($this->_expectations !== []) {
            $expectation = $this->_findExpectationIn($this->_expectations, $args);
        }

        if ($expectation === null && $this->_defaults !== []) {
            return $this->_findExpectationIn($this->_defaults, $args);
        }

        return $expectation;
    }

    /**
     * Return all expectations assigned to this director
	 * 返回指定给该董事的所有期望
     *
     * @return array<ExpectationInterface>
     */
    public function getDefaultExpectations()
    {
        return $this->_defaults;
    }

    /**
     * Return the number of expectations assigned to this director.
	 * 返回分配给这个负责人的期望的数量
     *
     * @return int
     */
    public function getExpectationCount()
    {
        $count = 0;

        $expectations = $this->getExpectations();

        if ($expectations === []) {
            $expectations = $this->getDefaultExpectations();
        }

        foreach ($expectations as $expectation) {
            if ($expectation->isCallCountConstrained()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Return all expectations assigned to this director
	 * 返回指定给该董事的所有期望
     *
     * @return array<ExpectationInterface>
     */
    public function getExpectations()
    {
        return $this->_expectations;
    }

    /**
     * Make the given expectation a default for all others assuming it was correctly created last
	 * 将给定的期望默认为所有其他人假设它是正确的
     *
     * @throws Exception
     *
     * @return void
     */
    public function makeExpectationDefault(Expectation $expectation)
    {
        if (end($this->_expectations) === $expectation) {
            array_pop($this->_expectations);

            array_unshift($this->_defaults, $expectation);

            return;
        }

        throw new Exception('Cannot turn a previously defined expectation into a default');
    }

    /**
     * Verify all expectations of the director
	 * 核实董事的所有期望
     *
     * @throws Exception
     *
     * @return void
     */
    public function verify()
    {
        if ($this->_expectations !== []) {
            foreach ($this->_expectations as $expectation) {
                $expectation->verify();
            }

            return;
        }

        foreach ($this->_defaults as $expectation) {
            $expectation->verify();
        }
    }

    /**
     * Search current array of expectations for a match
	 * 搜索当前对匹配的期望数组
     *
     * @param array<ExpectationInterface> $expectations
     *
     * @return null|ExpectationInterface
     */
    protected function _findExpectationIn(array $expectations, array $args)
    {
        foreach ($expectations as $expectation) {
            if (! $expectation->isEligible()) {
                continue;
            }

            if (! $expectation->matchArgs($args)) {
                continue;
            }

            return $expectation;
        }

        foreach ($expectations as $expectation) {
            if ($expectation->matchArgs($args)) {
                return $expectation;
            }
        }

        return null;
    }
}
