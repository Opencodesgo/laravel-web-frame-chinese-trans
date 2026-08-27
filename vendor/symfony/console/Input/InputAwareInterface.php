<?php
/**
 * Symfony，Component，Console，输入，输入感知接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Input;

/**
 * InputAwareInterface should be implemented by classes that depends on the
 * Console Input.
 * InputAwareInterface 应该由依赖控制台输入的类实现。
 *
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
interface InputAwareInterface
{
    /**
     * Sets the Console Input.
	 * 设置控制台输入
     *
     * @return void
     */
    public function setInput(InputInterface $input);
}
