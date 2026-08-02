<?php
/**
 * Symfony，Component，Console，Input，流式的输入接口
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
 * StreamableInputInterface is the interface implemented by all input classes
 * that have an input stream.
 * StreamableInputInterface是由所有输入类实现的接口
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface StreamableInputInterface extends InputInterface
{
    /**
     * Sets the input stream to read from when interacting with the user.
	 * 设置与用户交互时要读取的输入流
     *
     * This is mainly useful for testing purpose.
     *
     * @param resource $stream The input stream
     */
    public function setStream($stream);

    /**
     * Returns the input stream.
	 * 返回输入流
     *
     * @return resource|null
     */
    public function getStream();
}
