<?php
/**
 * Symfony，Component，Process，输入流
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

use Symfony\Component\Process\Exception\RuntimeException;

/**
 * Provides a way to continuously write to the input of a Process until the InputStream is closed.
 * 提供一种持续写入进程输入的方法，直到InputStream关闭。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @implements \IteratorAggregate<int, string>
 */
class InputStream implements \IteratorAggregate
{
    /** @var callable|null */
    private $onEmpty = null;
    private $input = [];
    private $open = true;

    /**
     * Sets a callback that is called when the write buffer becomes empty.
	 * 设置当写缓冲区变为空时调用的回调函数
     */
    public function onEmpty(?callable $onEmpty = null)
    {
        $this->onEmpty = $onEmpty;
    }

    /**
     * Appends an input to the write buffer.
	 * 将输入追加到写缓冲区
     *
     * @param resource|string|int|float|bool|\Traversable|null $input The input to append as scalar,
     *                                                                stream resource or \Traversable
     */
    public function write($input)
    {
        if (null === $input) {
            return;
        }
        if ($this->isClosed()) {
            throw new RuntimeException(sprintf('"%s" is closed.', static::class));
        }
        $this->input[] = ProcessUtils::validateInput(__METHOD__, $input);
    }

    /**
     * Closes the write buffer.
	 * 关闭写缓冲区
     */
    public function close()
    {
        $this->open = false;
    }

    /**
     * Tells whether the write buffer is closed or not.
	 * 告诉写缓冲区是否关闭
     */
    public function isClosed()
    {
        return !$this->open;
    }

    /**
     * @return \Traversable<int, string>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $this->open = true;

        while ($this->open || $this->input) {
            if (!$this->input) {
                yield '';
                continue;
            }
            $current = array_shift($this->input);

            if ($current instanceof \Iterator) {
                yield from $current;
            } else {
                yield $current;
            }
            if (!$this->input && $this->open && null !== $onEmpty = $this->onEmpty) {
                $this->write($onEmpty($this));
            }
        }
    }
}
