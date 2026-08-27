<?php
/**
 * Psy，异常，异常
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Exception;

/**
 * An interface for Psy Exceptions.
 * 一个用于Psy异常的接口。
 */
interface Exception
{
    /**
     * This is the only thing, really...
	 * 这是唯一的事情，真的…
     *
     * Return a raw (unformatted) version of the message.
     *
     * @return string
     */
    public function getRawMessage();
}
