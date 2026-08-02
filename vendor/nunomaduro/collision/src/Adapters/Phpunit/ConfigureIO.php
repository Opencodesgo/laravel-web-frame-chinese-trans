<?php
/**
 * PhpParser，碰撞，适配器，单元测试，配置输入输出
 */

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Adapters\Phpunit;

use ReflectionObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * @internal
 */
final class ConfigureIO
{
    /**
     * Configures both given input and output with
     * options from the enviroment.
	 * 根据环境中的选项配置给定的输入和输出
     *
     * @throws \ReflectionException
     */
    public static function of(InputInterface $input, Output $output): void
    {
        $application = new Application();
        $reflector   = new ReflectionObject($application);
        $method      = $reflector->getMethod('configureIO');
        $method->setAccessible(true);
        $method->invoke($application, $input, $output);
    }
}
