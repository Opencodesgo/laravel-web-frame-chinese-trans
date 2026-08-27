<?php declare(strict_types=1);

/**
 * Monolog，可重调接口
 */

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog;

/**
 * Handler or Processor implementing this interface will be reset when Logger::reset() is called.
 * 当调用Logger::reset()时，实现此接口的Handler或Processor将被重置。
 *
 * Resetting ends a log cycle gets them back to their initial state.
 *
 * Resetting a Handler or a Processor means flushing/cleaning all buffers, resetting internal
 * state, and getting it back to a state in which it can receive log records again.
 *
 * This is useful in case you want to avoid logs leaking between two requests or jobs when you
 * have a long running process like a worker or an application server serving multiple requests
 * in one process.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
interface ResettableInterface
{
    /**
     * @return void
     */
    public function reset();
}
