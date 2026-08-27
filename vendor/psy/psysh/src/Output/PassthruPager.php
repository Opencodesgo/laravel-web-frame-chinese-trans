<?php
/**
 * Psy，输出，Passthru 寻呼机
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Output;

use Symfony\Component\Console\Output\StreamOutput;

/**
 * A passthrough pager is a no-op. It simply wraps a StreamOutput's stream and
 * does nothing when the pager is closed.
 * 传呼是无操作的。它只是包装了一个StreamOutput的流和关闭寻呼机时不执行任何操作。
 */
class PassthruPager extends StreamOutput implements OutputPager
{
    /**
     * Constructor.
     *
     * @param StreamOutput $output
     */
    public function __construct(StreamOutput $output)
    {
        parent::__construct($output->getStream());
    }

    /**
     * Close the current pager process.
	 * 关闭当前寻呼机进程
     */
    public function close()
    {
        // nothing to do here
    }
}
