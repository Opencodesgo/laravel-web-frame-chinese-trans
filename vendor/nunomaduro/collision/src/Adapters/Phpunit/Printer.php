<?php
/**
 * NunoMaduro，Collision，适配器，Php单元，打印机
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Adapters\Phpunit;

use NunoMaduro\Collision\Exceptions\ShouldNotHappen;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use ReflectionObject;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

/**
 * @internal
 */
final class Printer implements \PHPUnit\TextUI\ResultPrinter
{
    /**
     * Holds an instance of the style.
	 * 保存样式的实例。
     *
     * Style is a class we use to interact with output.
	 * Style是一个我们用来与输出交互的类。
     *
     * @var Style
     */
    private $style;

    /**
     * Holds the duration time of the test suite.
	 * 保存测试套件的持续时间
     *
     * @var Timer
     */
    private $timer;

    /**
     * Holds the state of the test
     * suite. The number of tests, etc.
     *
     * @var State
     */
    private $state;

    /**
     * If the test suite has failed.
	 * 如果测试套件失败了
     *
     * @var bool
     */
    private $failed = false;

    /**
     * Creates a new instance of the listener.
	 * 创建侦听器的新实例。
     *
     * @param ConsoleOutput $output
     *
     * @throws \ReflectionException
     */
    public function __construct(\Symfony\Component\Console\Output\ConsoleOutputInterface $output = null, bool $verbose = false, string $colors = 'always')
    {
        $this->timer = Timer::start();

        $decorated = $colors === 'always' || $colors === 'auto';

        $output = $output ?? new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, $decorated);

        ConfigureIO::of(new ArgvInput(), $output);

        $this->style = new Style($output);
        $dummyTest   = new class() extends TestCase {
        };

        $this->state = State::from($dummyTest);
    }

    /**
     * {@inheritdoc}
     */
    public function addError(Test $testCase, Throwable $throwable, float $time): void
    {
        $this->failed = true;

        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, TestResult::FAIL, $throwable));
    }

    /**
     * {@inheritdoc}
     */
    public function addWarning(Test $testCase, Warning $warning, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, TestResult::WARN, $warning));
    }

    /**
     * {@inheritdoc}
     */
    public function addFailure(Test $testCase, AssertionFailedError $error, float $time): void
    {
        $this->failed = true;

        $testCase = $this->testCaseFromTest($testCase);

        $reflector = new ReflectionObject($error);

        if ($reflector->hasProperty('message')) {
            $message  = trim((string) preg_replace("/\r|\n/", "\n  ", $error->getMessage()));
            $property = $reflector->getProperty('message');
            $property->setAccessible(true);
            $property->setValue($error, $message);
        }

        $this->state->add(TestResult::fromTestCase($testCase, TestResult::FAIL, $error));
    }

    /**
     * {@inheritdoc}
     */
    public function addIncompleteTest(Test $testCase, Throwable $throwable, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, TestResult::INCOMPLETE, $throwable));
    }

    /**
     * {@inheritdoc}
     */
    public function addRiskyTest(Test $testCase, Throwable $throwable, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, TestResult::RISKY, $throwable));
    }

    /**
     * {@inheritdoc}
     */
    public function addSkippedTest(Test $testCase, Throwable $throwable, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        $this->state->add(TestResult::fromTestCase($testCase, TestResult::SKIPPED, $throwable));
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if ($this->state->suiteTotalTests === null) {
            $this->state->suiteTotalTests = $suite->count();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(TestSuite $suite): void
    {
        // ..
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(Test $testCase): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        // Let's check first if the testCase is over.
        if ($this->state->testCaseHasChanged($testCase)) {
            $this->style->writeCurrentTestCaseSummary($this->state);

            $this->state->moveTo($testCase);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function endTest(Test $testCase, float $time): void
    {
        $testCase = $this->testCaseFromTest($testCase);

        if (!$this->state->existsInTestCase($testCase)) {
            $this->state->add(TestResult::fromTestCase($testCase, TestResult::PASS));
        }

        if ($testCase instanceof TestCase
            && $testCase->getTestResultObject() instanceof \PHPUnit\Framework\TestResult
            && !$testCase->getTestResultObject()->isStrictAboutOutputDuringTests()
            && !$testCase->hasExpectationOnOutput()) {
            $this->style->write($testCase->getActualOutput());
        }
    }

    /**
     * Intentionally left blank as we output things on events of the listener.
	 * 当我们输出关于侦听器事件的内容时，故意保留空白。
     */
    public function write(string $content): void
    {
        // ..
    }

    /**
     * Returns a test case from the given test.
	 * 从给定的测试返回一个测试用例。
     *
     * Note: This printer is do not work with normal Test classes - only
     * with Test Case classes. Please report an issue if you think
     * this should work any other way.
     */
    private function testCaseFromTest(Test $test): TestCase
    {
        if (!$test instanceof TestCase) {
            throw new ShouldNotHappen();
        }

        return $test;
    }

    /**
     * Intentionally left blank as we output things on events of the listener.
	 * 当我们输出关于侦听器事件的内容时，故意保留空白。
     */
    public function printResult(\PHPUnit\Framework\TestResult $result): void
    {
        if ($result->count() === 0) {
            $this->style->writeWarning('No tests executed!');
        }

        $this->style->writeCurrentTestCaseSummary($this->state);

        if ($this->failed) {
            $onFailure = $this->state->suiteTotalTests !== $this->state->testSuiteTestsCount();
            $this->style->writeErrorsSummary($this->state, $onFailure);
        }

        $this->style->writeRecap($this->state, $this->timer);
    }
}
