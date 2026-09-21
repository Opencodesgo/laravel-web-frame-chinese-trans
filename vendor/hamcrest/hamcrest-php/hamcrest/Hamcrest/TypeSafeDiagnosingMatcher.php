<?php
/**
 * Hamcrest，类型安全诊断匹配器
 */

namespace Hamcrest;

/**
 * Convenient base class for Matchers that require a value of a specific type.
 * This simply checks the type and then casts.
 * 适用于需要特定类型值的匹配器的便捷基类。它只需检查类型，然后进行类型转换。
 */

abstract class TypeSafeDiagnosingMatcher extends TypeSafeMatcher
{

    final public function matchesSafely($item)
    {
        return $this->matchesSafelyWithDiagnosticDescription($item, new NullDescription());
    }

    final public function describeMismatchSafely($item, Description $mismatchDescription)
    {
        $this->matchesSafelyWithDiagnosticDescription($item, $mismatchDescription);
    }

    // -- Protected Methods

    /**
     * Subclasses should implement these. The item will already have been checked for
     * the specific type.
	 * 子类应实现这些方法。项目已针对特定类型进行了检查。
     */
    abstract protected function matchesSafelyWithDiagnosticDescription($item, Description $mismatchDescription);
}
