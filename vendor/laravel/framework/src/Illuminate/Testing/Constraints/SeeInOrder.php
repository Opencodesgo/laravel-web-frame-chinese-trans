<?php
/**
 * Illuminate，测试，约束，观察排序
 */

namespace Illuminate\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;
use ReflectionClass;

class SeeInOrder extends Constraint
{
    /**
     * The string under validation.
	 * 正在验证的字符串
     *
     * @var string
     */
    protected $content;

    /**
     * The last value that failed to pass validation.
	 * 最后一个未能通过验证的值
     *
     * @var string
     */
    protected $failedValue;

    /**
     * Create a new constraint instance.
	 * 创建一个新的约束实例
     *
     * @param  string  $content
     * @return void
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Determine if the rule passes validation.
	 * 确定规则是否通过验证
     *
     * @param  array  $values
     * @return bool
     */
    public function matches($values): bool
    {
        $position = 0;

        foreach ($values as $value) {
            if (empty($value)) {
                continue;
            }

            $valuePosition = mb_strpos($this->content, $value, $position);

            if ($valuePosition === false || $valuePosition < $position) {
                $this->failedValue = $value;

                return false;
            }

            $position = $valuePosition + mb_strlen($value);
        }

        return true;
    }

    /**
     * Get the description of the failure.
	 * 获取故障的描述
     *
     * @param  array  $values
     * @return string
     */
    public function failureDescription($values): string
    {
        return sprintf(
            'Failed asserting that \'%s\' contains "%s" in specified order.',
            $this->content,
            $this->failedValue
        );
    }

    /**
     * Get a string representation of the object.
	 * 获取对象的字符串表示形式
     *
     * @return string
     */
    public function toString(): string
    {
        return (new ReflectionClass($this))->name;
    }
}
