<?php
/**
 * Symfony，Component，HttpKernel，依赖注入，扩展
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;

/**
 * Allow adding classes to the class cache.
 * 允许向类缓存中添加类
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Extension extends BaseExtension
{
    private $annotatedClasses = [];

    /**
     * Gets the annotated classes to cache.
	 * 获取要缓存的带注释的类
     *
     * @return array
     */
    public function getAnnotatedClassesToCompile()
    {
        return $this->annotatedClasses;
    }

    /**
     * Adds annotated classes to the class cache.
	 * 将带注释的类添加到类缓存中
     *
     * @param array $annotatedClasses An array of class patterns
     */
    public function addAnnotatedClassesToCompile(array $annotatedClasses)
    {
        $this->annotatedClasses = array_merge($this->annotatedClasses, $annotatedClasses);
    }
}
