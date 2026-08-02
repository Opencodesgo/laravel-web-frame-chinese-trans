<?php
/**
 * Mockery，发生器，字符串操作，传递，移除最终通过的内置方法
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Generator\StringManipulation\Pass;

use Mockery\Generator\MockConfiguration;
use Mockery\Generator\TargetClassInterface;
use function preg_replace;

/**
 * The standard Mockery\Mock class includes some methods to ease mocking, such
 * as __wakeup, however if the target has a final __wakeup method, it can't be
 * mocked. This pass removes the builtin methods where they are final on the
 * 标准的嘲笑\ Mock类包括一些简化的方法,
 * target
 */
class RemoveBuiltinMethodsThatAreFinalPass implements Pass
{
    protected $methods = [
        '__wakeup' => '/public function __wakeup\(\)\s+\{.*?\}/sm',
        '__toString' => '/public function __toString\(\)\s+(:\s+string)?\s*\{.*?\}/sm',
    ];

    /**
     * @param  string $code
     * @return string
     */
    public function apply($code, MockConfiguration $config)
    {
        $target = $config->getTargetClass();

        if (! $target instanceof TargetClassInterface) {
            return $code;
        }

        foreach ($target->getMethods() as $method) {
            if (! $method->isFinal()) {
                continue;
            }

            if (! isset($this->methods[$method->getName()])) {
                continue;
            }

            $code = preg_replace($this->methods[$method->getName()], '', $code);
        }

        return $code;
    }
}
