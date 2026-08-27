<?php
/**
 * Symfony，Component，Process，过程工具包
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Process;

use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * ProcessUtils is a bunch of utility methods.
 * ProcessUtils是一堆实用程序方法。
 *
 * This class contains static methods only and is not meant to be instantiated.
 * 这个类只包含静态方法，不打算被实例化。
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class ProcessUtils
{
    /**
     * This class should not be instantiated.
	 * 不应该实例化这个类
     */
    private function __construct()
    {
    }

    /**
     * Validates and normalizes a Process input.
	 * 验证并规范化流程输入
     *
     * @param string $caller The name of method call that validates the input
     * @param mixed  $input  The input to validate
     *
     * @throws InvalidArgumentException In case the input is not valid
     */
    public static function validateInput(string $caller, mixed $input): mixed
    {
        if (null !== $input) {
            if (\is_resource($input)) {
                return $input;
            }
            if (\is_scalar($input)) {
                return (string) $input;
            }
            if ($input instanceof Process) {
                return $input->getIterator($input::ITER_SKIP_ERR);
            }
            if ($input instanceof \Iterator) {
                return $input;
            }
            if ($input instanceof \Traversable) {
                return new \IteratorIterator($input);
            }

            throw new InvalidArgumentException(\sprintf('"%s" only accepts strings, Traversable objects or stream resources.', $caller));
        }

        return $input;
    }
}
