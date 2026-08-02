<?php
/**
 * Mockery，加载器，Loader
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Loader;

use Mockery\Generator\MockDefinition;

interface Loader
{
    /**
     * Load the given mock definition
	 * 加载给定的模拟定义
     *
     * @return void
     */
    public function load(MockDefinition $definition);
}
