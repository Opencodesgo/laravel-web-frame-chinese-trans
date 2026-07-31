<?php
/**
 * Mockery，期待
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
use Hamcrest\Matcher;
use Hamcrest_Matcher;
use InvalidArgumentException;
use Mockery;
use Mockery\CountValidator\AtLeast;
use Mockery\CountValidator\AtMost;
use Mockery\CountValidator\Exact;
use Mockery\Matcher\AndAnyOtherArgs;
use Mockery\Matcher\AnyArgs;
use Mockery\Matcher\ArgumentListMatcher;
use Mockery\Matcher\MatcherInterface;
use Mockery\Matcher\MultiArgumentClosure;
use Mockery\Matcher\NoArgs;
use OutOfBoundsException;
use PHPUnit\Framework\Constraint\Constraint;
use Throwable;

use function array_key_exists;
use function array_search;
use function array_shift;
use function array_slice;
use function count;
use function current;
use function func_get_args;
use function get_class;
use function in_array;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;
use function trigger_error;

use const E_USER_DEPRECATED;

class Expectation implements ExpectationInterface
{
    public const ERROR_ZERO_INVOCATION = 'shouldNotReceive(), never(), times(0) chaining additional invocation count methods has been deprecated and will throw an exception in a future version of Mockery';

    /**
     * Actual count of calls to this expectation
	 * 对这个期望的调用的实际计数
     *
     * @var int
     */
    protected $_actualCount = 0;

    /**
     * Exception message
	 * 异常消息
     *
     * @var null|string
     */
    protected $_because = null;

    /**
     * Array of closures executed with given arguments to generate a result
     * to be returned
	 * 使用给定参数执行以生成结果的闭包数组待归还
     *
     * @var array
     */
    protected $_closureQueue = [];

    /**
     * The count validator class to use
	 * 使用计数验证器类
     *
     * @var string
     */
    protected $_countValidatorClass = Exact::class;

    /**
     * Count validator store
	 * 计数验证器存储
     *
     * @var array
     */
    protected $_countValidators = [];

    /**
     * Arguments expected by this expectation
	 * 期望的论点
     *
     * @var array
     */
    protected $_expectedArgs = [];

    /**
     * Expected count of calls to this expectation
	 * 预计对这个期望的调用数
     *
     * @var int
     */
    protected $_expectedCount = -1;

    /**
     * Flag indicating whether the order of calling is determined locally or
     * globally
     *
     * @var bool
     */
    protected $_globally = false;

    /**
     * Integer representing the call order of this expectation on a global basis
	 * 在全球基础上表示这种期望的调用顺序的整数
     *
     * @var int
     */
    protected $_globalOrderNumber = null;

    /**
     * Mock object to which this expectation belongs
	 * 模拟对象,这个期望属于
     *
     * @var LegacyMockInterface
     */
    protected $_mock = null;

    /**
     * Method name
	 * 方法名称
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Integer representing the call order of this expectation
	 * 表示期望的调用顺序的整数
     *
     * @var int
     */
    protected $_orderNumber = null;

    /**
     * Flag indicating if the return value should be obtained from the original
     * class method instead of returning predefined values from the return queue
	 * 标志，指示是否应该从原始返回值中获得返回值类方法，而不是从返回队列返回预定义值。
     *
     * @var bool
     */
    protected $_passthru = false;

    /**
     * Array of return values as a queue for multiple return sequence
	 * 作为多个返回序列队列的返回值数组
     *
     * @var array
     */
    protected $_returnQueue = [];

    /**
     * Value to return from this expectation
	 * 从这个期望中返回的值
     *
     * @var mixed
     */
    protected $_returnValue = null;

    /**
     * Array of values to be set when this expectation matches
	 * 当此期望匹配时要设置的值数组
     *
     * @var array
     */
    protected $_setQueue = [];

    /**
     * Flag indicating that an exception is expected to be throw (not returned)
	 * 表示预计会抛出（而不是返回）异常的标志
     *
     * @var bool
     */
    protected $_throw = false;

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct(LegacyMockInterface $mock, $name)
    {
        $this->_mock = $mock;
        $this->_name = $name;
        $this->withAnyArgs();
    }

    /**
     * Cloning logic
     */
    public function __clone()
    {
        $newValidators = [];

        $countValidators = $this->_countValidators;

        foreach ($countValidators as $validator) {
            $newValidators[] = clone $validator;
        }

        $this->_countValidators = $newValidators;
    }

    /**
     * Return a string with the method name and arguments formatted
	 * 返回方法名和参数格式化的字符串
     *
     * @return string
     */
    public function __toString()
    {
        return Mockery::formatArgs($this->_name, $this->_expectedArgs);
    }

    /**
     * Set a return value, or sequential queue of return values
	 * 设置返回值或返回值的顺序队列
     *
     * @param mixed ...$args
     *
     * @return self
     */
    public function andReturn(...$args)
    {
        $this->_returnQueue = $args;

        return $this;
    }

    /**
     * Sets up a closure to return the nth argument from the expected method call
	 * 设置闭包以返回预期方法调用的第n个参数
     *
     * @param int $index
     *
     * @return self
     */
    public function andReturnArg($index)
    {
        if (! is_int($index) || $index < 0) {
            throw new InvalidArgumentException(
                'Invalid argument index supplied. Index must be a non-negative integer.'
            );
        }

        $closure = static function (...$args) use ($index) {
            if (array_key_exists($index, $args)) {
                return $args[$index];
            }

            throw new OutOfBoundsException(
                'Cannot return an argument value. No argument exists for the index ' . $index
            );
        };

        $this->_closureQueue = [$closure];

        return $this;
    }

    /**
     * @return self
     */
    public function andReturnFalse()
    {
        return $this->andReturn(false);
    }

    /**
     * Return null. This is merely a language construct for Mock describing.
	 * 返回null。这仅仅是Mock描述的语言结构。
     *
     * @return self
     */
    public function andReturnNull()
    {
        return $this->andReturn(null);
    }

    /**
     * Set a return value, or sequential queue of return values
	 * 设置返回值或返回值的顺序队列
     *
     * @param mixed ...$args
     *
     * @return self
     */
    public function andReturns(...$args)
    {
        return $this->andReturn(...$args);
    }

    /**
     * Return this mock, like a fluent interface
	 * 返回这个模拟，就像一个流畅的接口。
     *
     * @return self
     */
    public function andReturnSelf()
    {
        return $this->andReturn($this->_mock);
    }

    /**
     * @return self
     */
    public function andReturnTrue()
    {
        return $this->andReturn(true);
    }

    /**
     * Return a self-returning black hole object.
	 * 返回一个自返回黑洞对象
     *
     * @return self
     */
    public function andReturnUndefined()
    {
        return $this->andReturn(new Undefined());
    }

    /**
     * Set a closure or sequence of closures with which to generate return
     * values. The arguments passed to the expected method are passed to the
     * closures as parameters.
	 * 设置要生成返回值的闭包或闭包序列值。
     *
     * @param callable ...$args
     *
     * @return self
     */
    public function andReturnUsing(...$args)
    {
        $this->_closureQueue = $args;

        return $this;
    }

    /**
     * Set a sequential queue of return values with an array
	 * 用数组设置返回值的顺序队列
     *
     * @return self
     */
    public function andReturnValues(array $values)
    {
        return $this->andReturn(...$values);
    }

    /**
     * Register values to be set to a public property each time this expectation occurs
	 * 每次发生此期望时，将注册值设置为公共属性。
     *
     * @param string $name
     * @param array  ...$values
     *
     * @return self
     */
    public function andSet($name, ...$values)
    {
        $this->_setQueue[$name] = $values;

        return $this;
    }

    /**
     * Set Exception class and arguments to that class to be thrown
	 * 设置Exception类和要抛出的该类的参数
     *
     * @param string|Throwable $exception
     * @param string           $message
     * @param int              $code
     *
     * @return self
     */
    public function andThrow($exception, $message = '', $code = 0, ?\Exception $previous = null)
    {
        $this->_throw = true;

        if (is_object($exception)) {
            return $this->andReturn($exception);
        }

        return $this->andReturn(new $exception($message, $code, $previous));
    }

    /**
     * Set Exception classes to be thrown
	 * 设置要抛出的Exception类
     *
     * @return self
     */
    public function andThrowExceptions(array $exceptions)
    {
        $this->_throw = true;

        foreach ($exceptions as $exception) {
            if (! is_object($exception)) {
                throw new Exception('You must pass an array of exception objects to andThrowExceptions');
            }
        }

        return $this->andReturnValues($exceptions);
    }

    public function andThrows($exception, $message = '', $code = 0, ?\Exception $previous = null)
    {
        return $this->andThrow($exception, $message, $code, $previous);
    }

    /**
     * Sets up a closure that will yield each of the provided args
	 * 设置一个闭包，该闭包将产生所提供的每个参数。
     *
     * @param mixed ...$args
     *
     * @return self
     */
    public function andYield(...$args)
    {
        $closure = static function () use ($args) {
            foreach ($args as $arg) {
                yield $arg;
            }
        };

        $this->_closureQueue = [$closure];

        return $this;
    }

    /**
     * Sets next count validator to the AtLeast instance
	 * 将下一个计数验证器设置为AtLeast实例
     *
     * @return self
     */
    public function atLeast()
    {
        $this->_countValidatorClass = AtLeast::class;

        return $this;
    }

    /**
     * Sets next count validator to the AtMost instance
	 * 将下一个计数验证器设置为AtMost实例
     *
     * @return self
     */
    public function atMost()
    {
        $this->_countValidatorClass = AtMost::class;

        return $this;
    }

    /**
     * Set the exception message
	 * 设置异常消息
     *
     * @param string $message
     *
     * @return $this
     */
    public function because($message)
    {
        $this->_because = $message;

        return $this;
    }

    /**
     * Shorthand for setting minimum and maximum constraints on call counts
	 * 对调用计数设置最小和最大约束的简写
     *
     * @param int $minimum
     * @param int $maximum
     */
    public function between($minimum, $maximum)
    {
        return $this->atLeast()->times($minimum)->atMost()->times($maximum);
    }

    /**
     * Mark this expectation as being a default
	 * 将此期望标记为默认值
     *
     * @return self
     */
    public function byDefault()
    {
        $director = $this->_mock->mockery_getExpectationsFor($this->_name);

        if ($director instanceof ExpectationDirector) {
            $director->makeExpectationDefault($this);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getExceptionMessage()
    {
        return $this->_because;
    }

    /**
     * Return the parent mock of the expectation
	 * 返回期望的父模拟
     *
     * @return LegacyMockInterface|MockInterface
     */
    public function getMock()
    {
        return $this->_mock;
    }

    public function getName()
    {
        return $this->_name;
    }

    /**
     * Return order number
	 * 退货单号
     *
     * @return int
     */
    public function getOrderNumber()
    {
        return $this->_orderNumber;
    }

    /**
     * Indicates call order should apply globally
	 * 指示呼叫顺序应全局应用
     *
     * @return self
     */
    public function globally()
    {
        $this->_globally = true;

        return $this;
    }

    /**
     * Check if there is a constraint on call count
	 * 检查呼叫计数是否有限制
     *
     * @return bool
     */
    public function isCallCountConstrained()
    {
        return $this->_countValidators !== [];
    }

    /**
     * Checks if this expectation is eligible for additional calls
	 * 检查此期望是否符合附加呼叫的条件
     *
     * @return bool
     */
    public function isEligible()
    {
        foreach ($this->_countValidators as $validator) {
            if (! $validator->isEligible($this->_actualCount)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if passed arguments match an argument expectation
	 * 检查传递的参数是否符合参数期望
     *
     * @return bool
     */
    public function matchArgs(array $args)
    {
        if ($this->isArgumentListMatcher()) {
            return $this->_matchArg($this->_expectedArgs[0], $args);
        }

        $argCount = count($args);

        $expectedArgsCount = count($this->_expectedArgs);

        if ($argCount === $expectedArgsCount) {
            return $this->_matchArgs($args);
        }

        $lastExpectedArgument = $this->_expectedArgs[$expectedArgsCount - 1];

        if ($lastExpectedArgument instanceof AndAnyOtherArgs) {
            $firstCorrespondingKey = array_search($lastExpectedArgument, $this->_expectedArgs, true);

            $args = array_slice($args, 0, $firstCorrespondingKey);

            return $this->_matchArgs($args);
        }

        return false;
    }

    /**
     * Indicates that this expectation is never expected to be called
	 * 指示永远不会调用此期望
     *
     * @return self
     */
    public function never()
    {
        return $this->times(0);
    }

    /**
     * Indicates that this expectation is expected exactly once
	 * 指示该期望只期望一次
     *
     * @return self
     */
    public function once()
    {
        return $this->times(1);
    }

    /**
     * Indicates that this expectation must be called in a specific given order
	 * 指示必须以特定的给定顺序调用此期望
     *
     * @param string $group Name of the ordered group
     *
     * @return self
     */
    public function ordered($group = null)
    {
        if ($this->_globally) {
            $this->_globalOrderNumber = $this->_defineOrdered($group, $this->_mock->mockery_getContainer());
        } else {
            $this->_orderNumber = $this->_defineOrdered($group, $this->_mock);
        }

        $this->_globally = false;

        return $this;
    }

    /**
     * Flag this expectation as calling the original class method with
     * the provided arguments instead of using a return value queue.
     *
     * @return self
     */
    public function passthru()
    {
        if ($this->_mock instanceof Mock) {
            throw new Exception(
                'Mock Objects not created from a loaded/existing class are incapable of passing method calls through to a parent class'
            );
        }

        $this->_passthru = true;

        return $this;
    }

    /**
     * Alias to andSet(). Allows the natural English construct
     * - set('foo', 'bar')->andReturn('bar')
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     */
    public function set($name, $value)
    {
        return $this->andSet(...func_get_args());
    }

    /**
     * Indicates the number of times this expectation should occur
	 * 指示此期望应该发生的次数
     *
     * @param int $limit
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public function times($limit = null)
    {
        if ($limit === null) {
            return $this;
        }

        if (! is_int($limit)) {
            throw new InvalidArgumentException('The passed Times limit should be an integer value');
        }

        if ($this->_expectedCount === 0) {
            @trigger_error(self::ERROR_ZERO_INVOCATION, E_USER_DEPRECATED);
            // throw new \InvalidArgumentException(self::ERROR_ZERO_INVOCATION);
        }

        if ($limit === 0) {
            $this->_countValidators = [];
        }

        $this->_expectedCount = $limit;

        $this->_countValidators[$this->_countValidatorClass] = new $this->_countValidatorClass($this, $limit);

        if ($this->_countValidatorClass !== Exact::class) {
            $this->_countValidatorClass = Exact::class;

            unset($this->_countValidators[$this->_countValidatorClass]);
        }

        return $this;
    }

    /**
     * Indicates that this expectation is expected exactly twice
	 * 指示该期望被精确地期望两次
     *
     * @return self
     */
    public function twice()
    {
        return $this->times(2);
    }

    /**
     * Verify call order
	 * 验证呼叫顺序
     *
     * @return void
     */
    public function validateOrder()
    {
        if ($this->_orderNumber) {
            $this->_mock->mockery_validateOrder((string) $this, $this->_orderNumber, $this->_mock);
        }

        if ($this->_globalOrderNumber) {
            $this->_mock->mockery_getContainer()->mockery_validateOrder(
                (string) $this,
                $this->_globalOrderNumber,
                $this->_mock
            );
        }
    }

    /**
     * Verify this expectation
	 * 验证这个期望
     *
     * @return void
     */
    public function verify()
    {
        foreach ($this->_countValidators as $validator) {
            $validator->validate($this->_actualCount);
        }
    }

    /**
     * Verify the current call, i.e. that the given arguments match those
     * of this expectation
     *
     * @throws Throwable
     *
     * @return mixed
     */
    public function verifyCall(array $args)
    {
        $this->validateOrder();

        ++$this->_actualCount;

        if ($this->_passthru === true) {
            return $this->_mock->mockery_callSubjectMethod($this->_name, $args);
        }

        $return = $this->_getReturnValue($args);

        $this->throwAsNecessary($return);

        $this->_setValues();

        return $return;
    }

    /**
     * Expected argument setter for the expectation
	 * 期望参数设置器
     *
     * @param mixed ...$args
     *
     * @return self
     */
    public function with(...$args)
    {
        return $this->withArgs($args);
    }

    /**
     * Set expectation that any arguments are acceptable
	 * 设定任何参数都是可接受的期望
     *
     * @return self
     */
    public function withAnyArgs()
    {
        $this->_expectedArgs = [new AnyArgs()];

        return $this;
    }

    /**
     * Expected arguments for the expectation passed as an array or a closure that matches each passed argument on
     * each function call.
     *
     * @param array|Closure $argsOrClosure
     *
     * @return self
     */
    public function withArgs($argsOrClosure)
    {
        if (is_array($argsOrClosure)) {
            return $this->withArgsInArray($argsOrClosure);
        }

        if ($argsOrClosure instanceof Closure) {
            return $this->withArgsMatchedByClosure($argsOrClosure);
        }

        throw new InvalidArgumentException(sprintf(
            'Call to %s with an invalid argument (%s), only array and closure are allowed',
            __METHOD__,
            $argsOrClosure
        ));
    }

    /**
     * Set with() as no arguments expected
	 * 设置with()为不需要参数
     *
     * @return self
     */
    public function withNoArgs()
    {
        $this->_expectedArgs = [new NoArgs()];

        return $this;
    }

    /**
     * Expected arguments should partially match the real arguments
	 * 预期参数应该部分匹配实际参数
     *
     * @param mixed ...$expectedArgs
     *
     * @return self
     */
    public function withSomeOfArgs(...$expectedArgs)
    {
        return $this->withArgs(static function (...$args) use ($expectedArgs): bool {
            foreach ($expectedArgs as $expectedArg) {
                if (! in_array($expectedArg, $args, true)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Indicates this expectation should occur zero or more times
	 * 指示此期望应该出现零次或多次
     *
     * @return self
     */
    public function zeroOrMoreTimes()
    {
        return $this->atLeast()->never();
    }

    /**
     * Setup the ordering tracking on the mock or mock container
	 * 在模拟或模拟容器上设置排序跟踪
     *
     * @param string $group
     * @param object $ordering
     *
     * @return int
     */
    protected function _defineOrdered($group, $ordering)
    {
        $groups = $ordering->mockery_getGroups();
        if ($group === null) {
            return $ordering->mockery_allocateOrder();
        }

        if (array_key_exists($group, $groups)) {
            return $groups[$group];
        }

        $result = $ordering->mockery_allocateOrder();

        $ordering->mockery_setGroup($group, $result);

        return $result;
    }

    /**
     * Fetch the return value for the matching args
	 * 获取匹配参数的返回值
     *
     * @return mixed
     */
    protected function _getReturnValue(array $args)
    {
        $closureQueueCount = count($this->_closureQueue);

        if ($closureQueueCount > 1) {
            return array_shift($this->_closureQueue)(...$args);
        }

        if ($closureQueueCount > 0) {
            return current($this->_closureQueue)(...$args);
        }

        $returnQueueCount = count($this->_returnQueue);

        if ($returnQueueCount > 1) {
            return array_shift($this->_returnQueue);
        }

        if ($returnQueueCount > 0) {
            return current($this->_returnQueue);
        }

        return $this->_mock->mockery_returnValueForMethod($this->_name);
    }

    /**
     * Check if passed argument matches an argument expectation
	 * 检查传递的参数是否与参数期望匹配
     *
     * @param mixed $expected
     * @param mixed $actual
     *
     * @return bool
     */
    protected function _matchArg($expected, &$actual)
    {
        if ($expected === $actual) {
            return true;
        }

        if ($expected instanceof MatcherInterface) {
            return $expected->match($actual);
        }

        if ($expected instanceof Constraint) {
            return (bool) $expected->evaluate($actual, '', true);
        }

        if ($expected instanceof Matcher || $expected instanceof Hamcrest_Matcher) {
            @trigger_error('Hamcrest package has been deprecated and will be removed in 2.0', E_USER_DEPRECATED);

            return $expected->matches($actual);
        }

        if (is_object($expected)) {
            $matcher = Mockery::getConfiguration()->getDefaultMatcher(get_class($expected));

            return $matcher === null ? false : $this->_matchArg(new $matcher($expected), $actual);
        }

        if (is_object($actual) && is_string($expected) && $actual instanceof $expected) {
            return true;
        }

        return $expected == $actual;
    }

    /**
     * Check if the passed arguments match the expectations, one by one.
	 * 逐个检查传递的参数是否符合预期
     *
     * @param array $args
     *
     * @return bool
     */
    protected function _matchArgs($args)
    {
        for ($index = 0, $argCount = count($args); $index < $argCount; ++$index) {
            $param = &$args[$index];

            if (! $this->_matchArg($this->_expectedArgs[$index], $param)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets public properties with queued values to the mock object
	 * 将具有排队值的公共属性设置为模拟对象
     *
     * @return void
     */
    protected function _setValues()
    {
        $mockClass = get_class($this->_mock);

        $container = $this->_mock->mockery_getContainer();

        $mocks = $container->getMocks();

        foreach ($this->_setQueue as $name => &$values) {
            if ($values === []) {
                continue;
            }

            $value = array_shift($values);

            $this->_mock->{$name} = $value;

            foreach ($mocks as $mock) {
                if (! $mock instanceof $mockClass) {
                    continue;
                }

                if (! $mock->mockery_isInstance()) {
                    continue;
                }

                $mock->{$name} = $value;
            }
        }
    }

    /**
     * @template TExpectedArg
     *
     * @param TExpectedArg $expectedArg
     *
     * @return bool
     */
    private function isAndAnyOtherArgumentsMatcher($expectedArg)
    {
        return $expectedArg instanceof AndAnyOtherArgs;
    }

    /**
     * Check if the registered expectation is an ArgumentListMatcher
	 * 检查注册的期望是否是一个ArgumentListMatcher
     *
     * @return bool
     */
    private function isArgumentListMatcher()
    {
        return $this->_expectedArgs !== [] && $this->_expectedArgs[0] instanceof ArgumentListMatcher;
    }

    /**
     * Throws an exception if the expectation has been configured to do so
	 * 如果期望已配置为这样做，则抛出异常。
     *
     * @param Throwable $return
     *
     * @throws Throwable
     *
     * @return void
     */
    private function throwAsNecessary($return)
    {
        if (! $this->_throw) {
            return;
        }

        if (! $return instanceof Throwable) {
            return;
        }

        throw $return;
    }

    /**
     * Expected arguments for the expectation passed as an array
	 * 作为数组传递的期望的预期参数
     *
     * @return self
     */
    private function withArgsInArray(array $arguments)
    {
        if ($arguments === []) {
            return $this->withNoArgs();
        }

        $this->_expectedArgs = $arguments;

        return $this;
    }

    /**
     * Expected arguments have to be matched by the given closure.
	 * 期望的参数必须与给定的闭包匹配
     *
     * @return self
     */
    private function withArgsMatchedByClosure(Closure $closure)
    {
        $this->_expectedArgs = [new MultiArgumentClosure($closure)];

        return $this;
    }
}
