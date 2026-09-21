<?php
/**
 * League，CommonMark，扩展，提及，生成器，回调函数生成器
 */

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Extension\Mention\Generator;

use League\CommonMark\Exception\LogicException;
use League\CommonMark\Extension\Mention\Mention;
use League\CommonMark\Node\Inline\AbstractInline;

final class CallbackGenerator implements MentionGeneratorInterface
{
    /**
     * A callback function which sets the URL on the passed mention and returns the mention, return a new AbstractInline based object or null if the mention is not a match
	 * 一个回调函数，它在传递的提及上设置URL并返回提及，返回一个新的基于AbstractInline的对象，如果提及不匹配则返回null。
     *
     * @var callable(Mention): ?AbstractInline
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @throws LogicException
     */
    public function generateMention(Mention $mention): ?AbstractInline
    {
        $result = \call_user_func($this->callback, $mention);
        if ($result === null) {
            return null;
        }

        if ($result instanceof AbstractInline && ! ($result instanceof Mention)) {
            return $result;
        }

        if ($result instanceof Mention && $result->hasUrl()) {
            return $mention;
        }

        throw new LogicException('CallbackGenerator callable must set the URL on the passed mention and return the mention, return a new AbstractInline based object or null if the mention is not a match');
    }
}
