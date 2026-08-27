<?php
/**
 * Mockery，配置
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
use LogicException;
use Mockery\Matcher\MatcherInterface;

use function array_key_exists;
use function array_merge;
use function class_implements;
use function get_parent_class;
use function is_a;
use function sprintf;
use function strtolower;
use function trigger_error;

use const E_USER_DEPRECATED;
use const PHP_MAJOR_VERSION;

class Configuration
{
    /**
     * Boolean assertion of whether we ignore unnecessary mocking of methods,
     * i.e. when method expectations are made, set using a zeroOrMoreTimes()
     * constraint, and then never called. Essentially such expectations are
     * not required and are just taking up test space.
	 * 布尔断言我们是否忽略了不必要的模拟方法，也就是说，当制定方法期望时，使用zeroOrMoreTimes()设置约束。
     *
     * @var bool
     */
    protected $_allowMockingMethodsUnnecessarily = true;

    /**
     * Boolean assertion of whether we can mock methods which do not actually
     * exist for the given class or object (ignored for unreal mocks)
	 * 布尔断言我们是否可以模拟不实际的方法。
     *
     * @var bool
     */
    protected $_allowMockingNonExistentMethod = true;

    /**
     * Constants map
	 * 常量映射
     *
     * e.g. ['class' => ['MY_CONST' => 123, 'OTHER_CONST' => 'foo']]
     *
     * @var array<class-string,array<string,array<scalar>|scalar>>
     */
    protected $_constantsMap = [];

    /**
     * Default argument matchers
	 * 默认参数匹配器
     *
     * e.g. ['class' => 'matcher']
     *
     * @var array<class-string,class-string>
     */
    protected $_defaultMatchers = [];

    /**
     * Parameter map for use with PHP internal classes.
	 * 用于PHP内部类的参数映射
     *
     *  e.g. ['class' => ['method' => ['param1', 'param2']]]
     *
     * @var array<class-string,array<string,list<string>>>
     */
    protected $_internalClassParamMap = [];

    /**
     * Custom object formatters
	 * 自定义对象格式化程序
     *
     * e.g. ['class' => static fn($object) => 'formatted']
     *
     * @var array<class-string,Closure>
     */
    protected $_objectFormatters = [];

    /**
     * @var QuickDefinitionsConfiguration
     */
    protected $_quickDefinitionsConfiguration;

    /**
     * Boolean assertion is reflection caching enabled or not. It should be
     * always enabled, except when using PHPUnit's --static-backup option.
	 * 布尔断言是否启用了反射缓存。
     *
     * @see https://github.com/mockery/mockery/issues/268
     */
    protected $_reflectionCacheEnabled = true;

    public function __construct()
    {
        $this->_quickDefinitionsConfiguration = new QuickDefinitionsConfiguration();
    }

    /**
     * Set boolean to allow/prevent unnecessary mocking of methods
	 * 设置boolean以允许/防止不必要的模拟方法
     *
     * @param bool $flag
     *
     * @return void
     *
     * @deprecated since 1.4.0
     */
    public function allowMockingMethodsUnnecessarily($flag = true)
    {
        @trigger_error(
            sprintf('The %s method is deprecated and will be removed in a future version of Mockery', __METHOD__),
            E_USER_DEPRECATED
        );

        $this->_allowMockingMethodsUnnecessarily = (bool) $flag;
    }

    /**
     * Set boolean to allow/prevent mocking of non-existent methods
	 * 设置boolean以允许/防止模拟不存在的方法
     *
     * @param bool $flag
     *
     * @return void
     */
    public function allowMockingNonExistentMethods($flag = true)
    {
        $this->_allowMockingNonExistentMethod = (bool) $flag;
    }

    /**
     * Disable reflection caching
	 * 禁用反射缓存
     *
     * It should be always enabled, except when using
     * PHPUnit's --static-backup option.
     *
     * @see https://github.com/mockery/mockery/issues/268
     *
     * @return void
     */
    public function disableReflectionCache()
    {
        $this->_reflectionCacheEnabled = false;
    }

    /**
     * Enable reflection caching
	 * 启用反射缓存
     *
     * It should be always enabled, except when using
     * PHPUnit's --static-backup option.
     *
     * @see https://github.com/mockery/mockery/issues/268
     *
     * @return void
     */
    public function enableReflectionCache()
    {
        $this->_reflectionCacheEnabled = true;
    }

    /**
     * Get the map of constants to be used in the mock generator
	 * 获取要在模拟生成器中使用的常量映射
     *
     * @return array<class-string,array<string,array<scalar>|scalar>>
     */
    public function getConstantsMap()
    {
        return $this->_constantsMap;
    }

    /**
     * Get the default matcher for a given class
	 * 获取给定类的默认匹配器
     *
     * @param class-string $class
     *
     * @return null|class-string
     */
    public function getDefaultMatcher($class)
    {
        $classes = [];

        $parentClass = $class;

        do {
            $classes[] = $parentClass;

            $parentClass = get_parent_class($parentClass);
        } while ($parentClass !== false);

        $classesAndInterfaces = array_merge($classes, class_implements($class));

        foreach ($classesAndInterfaces as $type) {
            if (array_key_exists($type, $this->_defaultMatchers)) {
                return $this->_defaultMatchers[$type];
            }
        }

        return null;
    }

    /**
     * Get the parameter map of an internal PHP class method
	 * 获取内部PHP类方法的参数映射
     *
     * @param class-string $class
     * @param string       $method
     *
     * @return null|array
     */
    public function getInternalClassMethodParamMap($class, $method)
    {
        $class = strtolower($class);
        $method = strtolower($method);
        if (! array_key_exists($class, $this->_internalClassParamMap)) {
            return null;
        }

        if (! array_key_exists($method, $this->_internalClassParamMap[$class])) {
            return null;
        }

        return $this->_internalClassParamMap[$class][$method];
    }

    /**
     * Get the parameter maps of internal PHP classes
	 * 获取内部PHP类的参数映射
     *
     * @return array<class-string,array<string,list<string>>>
     */
    public function getInternalClassMethodParamMaps()
    {
        return $this->_internalClassParamMap;
    }

    /**
     * Get the object formatter for a class
	 * 获取类的对象格式化程序
     *
     * @param class-string $class
     * @param Closure      $defaultFormatter
     *
     * @return Closure
     */
    public function getObjectFormatter($class, $defaultFormatter)
    {
        $parentClass = $class;

        do {
            $classes[] = $parentClass;

            $parentClass = get_parent_class($parentClass);
        } while ($parentClass !== false);

        $classesAndInterfaces = array_merge($classes, class_implements($class));

        foreach ($classesAndInterfaces as $type) {
            if (array_key_exists($type, $this->_objectFormatters)) {
                return $this->_objectFormatters[$type];
            }
        }

        return $defaultFormatter;
    }

    /**
     * Returns the quick definitions configuration
	 * 返回快速定义配置
     */
    public function getQuickDefinitions(): QuickDefinitionsConfiguration
    {
        return $this->_quickDefinitionsConfiguration;
    }

    /**
     * Return flag indicating whether mocking non-existent methods allowed
	 * 返回标志，指示是否允许模拟不存在的方法
     *
     * @return bool
     *
     * @deprecated since 1.4.0
     */
    public function mockingMethodsUnnecessarilyAllowed()
    {
        @trigger_error(
            sprintf('The %s method is deprecated and will be removed in a future version of Mockery', __METHOD__),
            E_USER_DEPRECATED
        );

        return $this->_allowMockingMethodsUnnecessarily;
    }

    /**
     * Return flag indicating whether mocking non-existent methods allowed
	 * 返回标志，指示是否允许模拟不存在的方法
     *
     * @return bool
     */
    public function mockingNonExistentMethodsAllowed()
    {
        return $this->_allowMockingNonExistentMethod;
    }

    /**
     * Is reflection cache enabled?
	 * 是否启用了反射缓存
     *
     * @return bool
     */
    public function reflectionCacheEnabled()
    {
        return $this->_reflectionCacheEnabled;
    }

    /**
     * Remove all overridden parameter maps from internal PHP classes.
	 * 从内部PHP类中删除所有覆盖的参数映射
     *
     * @return void
     */
    public function resetInternalClassMethodParamMaps()
    {
        $this->_internalClassParamMap = [];
    }

    /**
     * Set a map of constants to be used in the mock generator
	 * 设置要在模拟生成器中使用的常量映射
     *
     * e.g. ['MyClass' => ['MY_CONST' => 123, 'ARRAY_CONST' => ['foo', 'bar']]]
     *
     * @param array<class-string,array<string,array<scalar>|scalar>> $map
     *
     * @return void
     */
    public function setConstantsMap(array $map)
    {
        $this->_constantsMap = $map;
    }

    /**
     * @param class-string $class
     * @param class-string $matcherClass
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function setDefaultMatcher($class, $matcherClass)
    {
        $isHamcrest = is_a($matcherClass, Matcher::class, true)
            || is_a($matcherClass, Hamcrest_Matcher::class, true);

        if ($isHamcrest) {
            @trigger_error('Hamcrest package has been deprecated and will be removed in 2.0', E_USER_DEPRECATED);
        }

        if (! $isHamcrest && ! is_a($matcherClass, MatcherInterface::class, true)) {
            throw new InvalidArgumentException(sprintf(
                "Matcher class must implement %s, '%s' given.",
                MatcherInterface::class,
                $matcherClass
            ));
        }

        $this->_defaultMatchers[$class] = $matcherClass;
    }

    /**
     * Set a parameter map (array of param signature strings) for the method of an internal PHP class.
	 * 为内部PHP类的方法设置一个参数映射（参数签名字符串数组）
     *
     * @param class-string $class
     * @param string       $method
     * @param list<string> $map
     *
     * @throws LogicException
     *
     * @return void
     */
    public function setInternalClassMethodParamMap($class, $method, array $map)
    {
        if (PHP_MAJOR_VERSION > 7) {
            throw new LogicException(
                'Internal class parameter overriding is not available in PHP 8. Incompatible signatures have been reclassified as fatal errors.'
            );
        }

        $class = strtolower($class);

        if (! array_key_exists($class, $this->_internalClassParamMap)) {
            $this->_internalClassParamMap[$class] = [];
        }

        $this->_internalClassParamMap[$class][strtolower($method)] = $map;
    }

    /**
     * Set a custom object formatter for a class
	 * 为类设置自定义对象格式化程序
     *
     * @param class-string $class
     * @param Closure      $formatterCallback
     *
     * @return void
     */
    public function setObjectFormatter($class, $formatterCallback)
    {
        $this->_objectFormatters[$class] = $formatterCallback;
    }
}
