<?php
/**
 * Illuminate，测试，约束，数组子集
 */

namespace Illuminate\Testing\Constraints;

use ArrayObject;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Runner\Version;
use SebastianBergmann\Comparator\ComparisonFailure;
use Traversable;

if (class_exists(Version::class) && (int) Version::series()[0] >= 9) {
    /**
     * @internal This class is not meant to be used or overwritten outside the framework itself.
	 * 这个类不打算在框架本身之外使用或覆盖
     */
    final class ArraySubset extends Constraint
    {
        /**
         * @var iterable
         */
        private $subset;

        /**
         * @var bool
         */
        private $strict;

        /**
         * Create a new array subset constraint instance.
		 * 创建一个新的数组子集约束实例
         *
         * @param  iterable  $subset
         * @param  bool  $strict
         * @return void
         */
        public function __construct(iterable $subset, bool $strict = false)
        {
            $this->strict = $strict;
            $this->subset = $subset;
        }

        /**
         * Evaluates the constraint for parameter $other.
		 * 计算参数$other的约束
         *
         * If $returnResult is set to false (the default), an exception is thrown
         * in case of a failure. null is returned otherwise.
		 * 如果$returnResult设置为false（默认值），则抛出异常。
         *
         * If $returnResult is true, the result of the evaluation is returned as
         * a boolean value instead: true in case of success, false in case of a
         * failure.
		 * 如果$returnResult为true，则计算结果返回为布尔值。
         *
         * @param  mixed  $other
         * @param  string  $description
         * @param  bool  $returnResult
         * @return bool|null
         *
         * @throws \PHPUnit\Framework\ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         */
        public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
        {
            // type cast $other & $this->subset as an array to allow
            // support in standard array functions.
			// 将$other & $this->子集强制转换为数组以允许
            $other = $this->toArray($other);
            $this->subset = $this->toArray($this->subset);

            $patched = array_replace_recursive($other, $this->subset);

            if ($this->strict) {
                $result = $other === $patched;
            } else {
                $result = $other == $patched;
            }

            if ($returnResult) {
                return $result;
            }

            if (! $result) {
                $f = new ComparisonFailure(
                    $patched,
                    $other,
                    var_export($patched, true),
                    var_export($other, true)
                );

                $this->fail($other, $description, $f);
            }

            return null;
        }

        /**
         * Returns a string representation of the constraint.
		 * 返回约束的字符串表示形式
         *
         * @return string
         *
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         */
        public function toString(): string
        {
            return 'has the subset '.$this->exporter()->export($this->subset);
        }

        /**
         * Returns the description of the failure.
		 * 返回失败的描述
         *
         * The beginning of failure messages is "Failed asserting that" in most
         * cases. This method should return the second part of that sentence.
		 * 在大多数情况下，失败消息的开头是"失败的断言"。
         *
         * @param  mixed  $other
         * @return string
         *
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         */
        protected function failureDescription($other): string
        {
            return 'an array '.$this->toString();
        }

        /**
         * Returns the description of the failure.
		 * 返回失败的描述
         *
         * The beginning of failure messages is "Failed asserting that" in most
         * cases. This method should return the second part of that sentence.
		 * 在大多数情况下，失败消息的开头是"失败的断言"。
         *
         * @param  iterable  $other
         * @return array
         */
        private function toArray(iterable $other): array
        {
            if (is_array($other)) {
                return $other;
            }

            if ($other instanceof ArrayObject) {
                return $other->getArrayCopy();
            }

            if ($other instanceof Traversable) {
                return iterator_to_array($other);
            }

            // Keep BC even if we know that array would not be the expected one
            return (array) $other;
        }
    }
} else {
    /**
     * @internal This class is not meant to be used or overwritten outside the framework itself.
     */
    final class ArraySubset extends Constraint
    {
        /**
         * @var iterable
         */
        private $subset;

        /**
         * @var bool
         */
        private $strict;

        /**
         * Create a new array subset constraint instance.
		 * 创建一个新的数组子集约束实例
         *
         * @param  iterable  $subset
         * @param  bool  $strict
         * @return void
         */
        public function __construct(iterable $subset, bool $strict = false)
        {
            $this->strict = $strict;
            $this->subset = $subset;
        }

        /**
         * Evaluates the constraint for parameter $other.
		 * 计算参数$other的约束
         *
         * If $returnResult is set to false (the default), an exception is thrown
         * in case of a failure. null is returned otherwise.
		 * 如果$returnResult设置为false(默认值)，则抛出异常。
         *
         * If $returnResult is true, the result of the evaluation is returned as
         * a boolean value instead: true in case of success, false in case of a
         * failure.
         *
         * @param  mixed  $other
         * @param  string  $description
         * @param  bool  $returnResult
         * @return bool|null
         *
         * @throws \PHPUnit\Framework\ExpectationFailedException
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         */
        public function evaluate($other, string $description = '', bool $returnResult = false)
        {
            // type cast $other & $this->subset as an array to allow
            // support in standard array functions.
            $other = $this->toArray($other);
            $this->subset = $this->toArray($this->subset);

            $patched = array_replace_recursive($other, $this->subset);

            if ($this->strict) {
                $result = $other === $patched;
            } else {
                $result = $other == $patched;
            }

            if ($returnResult) {
                return $result;
            }

            if (! $result) {
                $f = new ComparisonFailure(
                    $patched,
                    $other,
                    var_export($patched, true),
                    var_export($other, true)
                );

                $this->fail($other, $description, $f);
            }
        }

        /**
         * Returns a string representation of the constraint.
		 * 返回约束的字符串表示形式
         *
         * @return string
         *
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         */
        public function toString(): string
        {
            return 'has the subset '.$this->exporter()->export($this->subset);
        }

        /**
         * Returns the description of the failure.
		 * 返回失败的描述
         *
         * The beginning of failure messages is "Failed asserting that" in most
         * cases. This method should return the second part of that sentence.
		 * 在大多数情况下，失败消息的开头是"失败的断言"。
         *
         * @param  mixed  $other
         * @return string
         *
         * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
         */
        protected function failureDescription($other): string
        {
            return 'an array '.$this->toString();
        }

        /**
         * Returns the description of the failure.
		 * 返回失败的描述
         *
         * The beginning of failure messages is "Failed asserting that" in most
         * cases. This method should return the second part of that sentence.
         *
         * @param  iterable  $other
         * @return array
         */
        private function toArray(iterable $other): array
        {
            if (is_array($other)) {
                return $other;
            }

            if ($other instanceof ArrayObject) {
                return $other->getArrayCopy();
            }

            if ($other instanceof Traversable) {
                return iterator_to_array($other);
            }

            // Keep BC even if we know that array would not be the expected one
			// 保留BC，即使我们知道该数组不是期望的数组。
            return (array) $other;
        }
    }
}
