<?php
/**
 * Mockery，适配器，单元测试，Mockery PHP单元集成
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Adapter\Phpunit;

use Mockery;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

use function method_exists;

/**
 * Integrates Mockery into PHPUnit. Ensures Mockery expectations are verified
 * for each test and are included by the assertion counter.
 * 将 Mockery 集成到 PHPUnit 中，确保每次测试时都会验证 Mockery 的预期，并将其计入断言计数器。
 */
trait MockeryPHPUnitIntegration
{
    use MockeryPHPUnitIntegrationAssertPostConditions;

    protected $mockeryOpen;

    protected function addMockeryExpectationsToAssertionCount()
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    protected function checkMockeryExceptions()
    {
        if (! method_exists($this, 'markAsRisky')) {
            return;
        }

        foreach (Mockery::getContainer()->mockery_thrownExceptions() as $e) {
            if (! $e->dismissed()) {
                $this->markAsRisky();
            }
        }
    }

    protected function closeMockery()
    {
        Mockery::close();
        $this->mockeryOpen = false;
    }

    /**
     * Performs assertions shared by all tests of a test case. This method is
     * called before execution of a test ends and before the tearDown method.
     */
    protected function mockeryAssertPostConditions()
    {
        $this->addMockeryExpectationsToAssertionCount();
        $this->checkMockeryExceptions();
        $this->closeMockery();

        parent::assertPostConditions();
    }

    /**
     * @after
     */
    #[After]
    protected function purgeMockeryContainer()
    {
        if ($this->mockeryOpen) {
            // post conditions wasn't called, so test probably failed
            Mockery::close();
        }
    }

    /**
     * @before
     */
    #[Before]
    protected function startMockery()
    {
        $this->mockeryOpen = true;
    }
}
