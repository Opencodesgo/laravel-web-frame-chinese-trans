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
     *
     * @return self
     */
    public function byDefault();

    /**
     * Set mock to defer unexpected methods to its parent if possible
	 * 如果可能的话,设置模拟将意想不到的方法推迟到它的父母
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
	 * 找到与给定方法和参数匹配的期望
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
	 * 将容器返回为这个模拟
     *
     * @return Container
     */
    public function mockery_getContainer();

    /**
     * Get current ordered number
	 * 得到当前有序数
     *
     * @return int
     */
    public function mockery_getCurrentOrder();

    /**
     * Gets the count of expectations for this mock
	 * 得到这个模拟的期望值
     *
     * @return int
     */
    public function mockery_getExpectationCount();

    /**
     * Return the expectations director for the given method
	 * 返回给定方法的期望主管
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
	 * 返回这个模拟的名称
     *
     * @return string
     */
    public function mockery_getName();

    /**
     * Alternative setup method to constructor
	 * 向构造函数的替代设置方法
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
	 * 设置当前顺序号
     *
     * @param int $order
     *
     * @return int
     */
    public function mockery_setCurrentOrder($order);

    /**
     * Return the expectations director for the given method
	 * 返回给定方法的期望主管
     *
     * @param string $method
     *
     * @return null|ExpectationDirector
     */
    public function mockery_setExpectationsFor($method, ExpectationDirector $director);

    /**
     * Set ordering for a group
	 * 为一个组设置排序
     *
     * @param string $group
     * @param int    $order
     *
     * @return void
     */
    public function mockery_setGroup($group, $order);

    /**
     * Tear down tasks for this mock
	 * 为这个模拟拆卸任务
     *
     * @return void
     */
    public function mockery_teardown();

    /**
     * Validate the current mock's ordering
	 * 验证当前模拟的排序
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
	 * 遍历所有期望董事,并验证每一个
     *
     * @throws Throwable
     *
     * @return void
     */
    public function mockery_verify();

    /**
     * Allows additional methods to be mocked that do not explicitly exist on mocked class
	 * 允许其他的方法被嘲笑,在被嘲笑的类上没有显式地存在
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
	 * 如果可能的话,设置模拟将意想不到的方法推迟到它的父母
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
	 * 设置模拟忽略意想不到的方法并返回未定义的类
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
	 * 为设定一种不应该调用方法的预期快捷方法
     *
     * @param string ...$methodNames one or many methods that are expected not to be called in this mock
     *
     * @return Expectation|ExpectationInterface|HigherOrderMessage
     */
    public function shouldNotReceive(...$methodNames);

    /**
     * Set expected method calls
	 * 设置预期方法调用
     *
     * @param string ...$methodNames one or many methods that are expected to be called in this mock
     *
     * @return Expectation|ExpectationInterface|HigherOrderMessage
     */
    public function shouldReceive(...$methodNames);
}
