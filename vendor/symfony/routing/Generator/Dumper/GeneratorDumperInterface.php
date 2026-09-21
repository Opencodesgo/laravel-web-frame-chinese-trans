<?php
/**
 * Symfony，Component，Routing，生成器，转储器，生成器转储接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Generator\Dumper;

use Symfony\Component\Routing\RouteCollection;

/**
 * GeneratorDumperInterface is the interface that all generator dumper classes must implement.
 * GeneratorDumperInterface是所有生成器转储类必须实现的接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface GeneratorDumperInterface
{
    /**
     * Dumps a set of routes to a string representation of executable code
     * that can then be used to generate a URL of such a route.
	 * 将一组路由转换为可执行代码的字符串表示形式，以便生成该路由的URL。
     */
    public function dump(array $options = []): string;

    /**
     * Gets the routes to dump.
	 * 获取要转储的路由
     */
    public function getRoutes(): RouteCollection;
}
