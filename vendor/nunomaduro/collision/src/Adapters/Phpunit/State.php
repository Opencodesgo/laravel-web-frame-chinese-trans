<?php
/**
 * NunoMaduro，Collision，适配器，Php单元，状态
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Adapters\Phpunit;

use NunoMaduro\Collision\Contracts\Adapters\Phpunit\HasPrintableTestCaseName;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class State
{
    /**
     * The complete test suite number of tests.
	 * 完整的测试套件数量的测试
     *
     * @var int|null
     */
    public $suiteTotalTests;

    /**
     * The complete test suite tests.
	 * 完整的测试套件测试
     *
     * @var array<int, TestResult>
     */
    public $suiteTests = [];

    /**
     * The current test case class.
	 * 当前测试用例类
     *
     * @var string
     */
    public $testCaseName;

    /**
     * The current test case tests.
	 * 当前测试用例进行测试
     *
     * @var array<int, TestResult>
     */
    public $testCaseTests = [];

    /**
     * The current test case tests.
	 * 当前测试用例进行测试
     *
     * @var array<int, TestResult>
     */
    public $toBePrintedCaseTests = [];

    /**
     * Header printed.
	 * 头印刷
     *
     * @var bool
     */
    public $headerPrinted = false;

    /**
     * The state constructor.
	 * 状态构造函数
     */
    private function __construct(string $testCaseName)
    {
        $this->testCaseName = $testCaseName;
    }

    /**
     * Creates a new State starting from the given test case.
	 * 从给定的测试用例开始创建一个新的状态
     */
    public static function from(TestCase $test): self
    {
        return new self(self::getPrintableTestCaseName($test));
    }

    /**
     * Adds the given test to the State.
	 * 将给定的测试添加到状态
     */
    public function add(TestResult $test): void
    {
        $this->testCaseTests[]        = $test;
        $this->toBePrintedCaseTests[] = $test;

        $this->suiteTests[] = $test;
    }

    /**
     * Gets the test case title.
	 * 获取测试用例标题
     */
    public function getTestCaseTitle(): string
    {
        foreach ($this->testCaseTests as $test) {
            if ($test->type === TestResult::FAIL) {
                return 'FAIL';
            }
        }

        foreach ($this->testCaseTests as $test) {
            if ($test->type !== TestResult::PASS) {
                return 'WARN';
            }
        }

        return 'PASS';
    }

    /**
     * Gets the test case title color.
	 * 获取测试用例标题颜色
     */
    public function getTestCaseTitleColor(): string
    {
        foreach ($this->testCaseTests as $test) {
            if ($test->type === TestResult::FAIL) {
                return 'red';
            }
        }

        foreach ($this->testCaseTests as $test) {
            if ($test->type !== TestResult::PASS) {
                return 'yellow';
            }
        }

        return 'green';
    }

    /**
     * Returns the number of tests on the current test case.
	 * 返回当前测试用例上的测试数
     */
    public function testCaseTestsCount(): int
    {
        return count($this->testCaseTests);
    }

    /**
     * Returns the number of tests on the complete test suite.
	 * 返回整个测试套件上的测试数
     */
    public function testSuiteTestsCount(): int
    {
        return count($this->suiteTests);
    }

    /**
     * Checks if the given test case is different from the current one.
	 * 检查给定的测试用例是否与当前的不同
     */
    public function testCaseHasChanged(TestCase $testCase): bool
    {
        return self::getPrintableTestCaseName($testCase) !== $this->testCaseName;
    }

    /**
     * Moves the a new test case.
	 * 移动一个新的测试用例
     */
    public function moveTo(TestCase $testCase): void
    {
        $this->testCaseName = self::getPrintableTestCaseName($testCase);

        $this->testCaseTests = [];

        $this->headerPrinted = false;
    }

    /**
     * Foreach test in the test case.
	 * 对于测试用例中的每个测试
     */
    public function eachTestCaseTests(callable $callback): void
    {
        foreach ($this->toBePrintedCaseTests as $test) {
            $callback($test);
        }

        $this->toBePrintedCaseTests = [];
    }

    public function countTestsInTestSuiteBy(string $type): int
    {
        return count(array_filter($this->suiteTests, function (TestResult $testResult) use ($type) {
            return $testResult->type === $type;
        }));
    }

    /**
     * Checks if the given test already contains a result.
	 * 检查给定的测试是否已经包含结果
     */
    public function existsInTestCase(TestCase $test): bool
    {
        foreach ($this->testCaseTests as $testResult) {
            if (TestResult::makeDescription($test) === $testResult->description) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the printable test case name from the given `TestCase`.
	 * 从给定的TestCase中返回可打印的测试用例名称
     */
    public static function getPrintableTestCaseName(TestCase $test): string
    {
        return $test instanceof HasPrintableTestCaseName
            ? $test->getPrintableTestCaseName()
            : get_class($test);
    }
}
