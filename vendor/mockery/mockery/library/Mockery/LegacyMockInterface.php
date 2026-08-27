<?php
/**
 * Mockery，Legacy 模拟接口
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery;

use Closure;
use Throwable;

interface LegacyMockInterface
{
    /**
     * In the event shouldReceive() accepting an array of methods/returns
     * this method will switch them from normal expectations to default
     * expectations
	 * 如果 eventReceive() 接受一个方法/返回值的数组，此方法将把它们从常规预期切换为默认预期。
     *
     * @return self
     */
    public function byDefault();

    /**
     * Set mock to defer unexpected methods to its parent if possible
	 * 如果可能的话，将mock设置为将意外的方法推迟给它的父方法。
     *
     * @return self
     */
    public function makePartial();

    /**
     * Fetch the next available allocation order number
	 * 获取下一个可用的分配订单号
     *
     * @return int
     */
    public function mockery_allocateOrder();

    /**
     * Find an expectation matching the given method and arguments
	 * 查找与给定方法和参数匹配的期望
     *
     * @template TMixed
     *
     * @param string        $method
     * @param array<TMixed> $args
     *
     * @return null|Expectation
     */
    public function mockery_findExpectation($method, array $args);

    /**
     * Return the container for this mock
	 * 返回此模拟的容器
     *
     * @return Container
     */
    public function mockery_getContainer();

    /**
     * Get current ordered number
	 * 获取当前订购编号
     *
     * @return int
     */
    public function mockery_getCurrentOrder();

    /**
     * Gets the count of expectations for this mock
	 * 获取此模拟的期望计数
     *
     * @return int
     */
    public function mockery_getExpectationCount();

    /**
     * Return the expectations director for the given method
	 * 返回给定方法的期望方向
     *
     * @param string $method
     *
     * @return null|ExpectationDirector
     */
    public function mockery_getExpectationsFor($method);

    /**
     * Fetch array of ordered groups
	 * 获取有序组的数组
     *
     * @return array<string,int>
     */
    public function mockery_getGroups();

    /**
     * @return string[]
     */
    public function mockery_getMockableMethods();

    /**
     * @return array
     */
    public function mockery_getMockableProperties();

    /**
     * Return the name for this mock
	 * 返回模拟的名称
     *
     * @return string
     */
    public function mockery_getName();

    /**
     * Alternative setup method to constructor
	 * 构造函数的可选设置方法
     *
     * @param object $partialObject
     *
     * @return void
     */
    public function mockery_init(?Container $container = null, $partialObject = null);

    /**
     * @return bool
     */
    public function mockery_isAnonymous();

    /**
     * Set current ordered number
	 * 设置当前有序数
     *
     * @param int $order
     *
     * @return int
     */
    public function mockery_setCurrentOrder($order);

    /**
     * Return the expectations director for the given method
	 * 返回给定方法的期望方向
     *
     * @param string $method
     *
     * @return null|ExpectationDirector
     */
    public function mockery_setExpectationsFor($method, ExpectationDirector $director);

    /**
     * Set ordering for a group
	 * 为组设置排序
     *
     * @param string $group
     * @param int    $order
     *
     * @return void
     */
    public function mockery_setGroup($group, $order);

    /**
     * Tear down tasks for this mock
	 * 删除此模拟的任务
     *
     * @return void
     */
    public function mockery_teardown();

    /**
     * Validate the current mock's ordering
	 * 验证当前mock的顺序
     *
     * @param string $method
     * @param int    $order
     *
     * @throws Exception
     *
     * @return void
     */
    public function mockery_validateOrder($method, $order);

    /**
     * Iterate across all expectation directors and validate each
	 * 遍历所有期望董事并验证每个董事
     *
     * @throws Throwable
     *
     * @return void
     */
    public function mockery_verify();

    /**
     * Allows additional methods to be mocked that do not explicitly exist on mocked class
	 * 允许模拟未显式存在于模拟类中的其他方法
     *
     * @param  string $method the method name to be mocked
     * @return self
     */
    public function shouldAllowMockingMethod($method);

    /**
     * @return self
     */
    public function shouldAllowMockingProtectedMethods();

    /**
     * Set mock to defer unexpected methods to its parent if possible
	 * 如果可能的话，将mock设置为将意外的方法推迟给它的父方法。
     *
     * @deprecated since 1.4.0. Please use makePartial() instead.
     *
     * @return self
     */
    public function shouldDeferMissing();

    /**
     * @return self
     */
    public function shouldHaveBeenCalled();

    /**
     * @template TMixed
     * @param string                     $method
     * @param null|array<TMixed>|Closure $args
     *
     * @return self
     */
    public function shouldHaveReceived($method, $args = null);

    /**
     * Set mock to ignore unexpected methods and return Undefined class
	 * 将mock设置为忽略意外方法并返回未定义类
     *
     * @template TReturnValue
     *
     * @param null|TReturnValue $returnValue the default return value for calls to missing functions on this mock
     *
     * @return self
     */
    public function shouldIgnoreMissing($returnValue = null);

    /**
     * @template TMixed
     * @param null|array<TMixed> $args (optional)
     *
     * @return self
     */
    public function shouldNotHaveBeenCalled(?array $args = null);

    /**
     * @template TMixed
     * @param string                     $method
     * @param null|array<TMixed>|Closure $args
     *
     * @return self
     */
    public function shouldNotHaveReceived($method, $args = null);

    /**
     * Shortcut method for setting an expectation that a method should not be called.
	 * 用于设置不应调用方法的期望的快捷方法。
     *
     * @param string ...$methodNames one or many methods that are expected not to be called in this mock
     *
     * @return Expectation|ExpectationInterface|HigherOrderMessage
     */
    public function shouldNotReceive(...$methodNames);

    /**
     * Set expected method calls
	 * 设置预期的方法调用
     *
     * @param string ...$methodNames one or many methods that are expected to be called in this mock
     *
     * @return Expectation|ExpectationInterface|HigherOrderMessage
     */
    public function shouldReceive(...$methodNames);
}
