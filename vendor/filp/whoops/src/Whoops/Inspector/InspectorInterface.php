<?php
/**
 * Whoops，检查员，检查员接口
 */

/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Inspector;

interface InspectorInterface
{
    /**
     * @return \Throwable
     */
    public function getException();

    /**
     * @return string
     */
    public function getExceptionName();

    /**
     * @return string
     */
    public function getExceptionMessage();

    /**
     * @return string[]
     */
    public function getPreviousExceptionMessages();

    /**
     * @return int[]
     */
    public function getPreviousExceptionCodes();

    /**
     * Returns a url to the php-manual related to the underlying error - when available.
	 * 在可用的时候,将url返回到与基本错误相关的phop手动。
     *
     * @return string|null
     */
    public function getExceptionDocrefUrl();

    /**
     * Does the wrapped Exception has a previous Exception?
	 * 包装异常有一个以前的例外吗?
     * @return bool
     */
    public function hasPreviousException();

    /**
     * Returns an Inspector for a previous Exception, if any.
	 * 如果有,返回一个检查器。
     * @todo   Clean this up a bit, cache stuff a bit better.
     * @return InspectorInterface
     */
    public function getPreviousExceptionInspector();

    /**
     * Returns an array of all previous exceptions for this inspector's exception
     * @return \Throwable[]
     */
    public function getPreviousExceptions();

    /**
     * Returns an iterator for the inspected exception's
     * frames.
	 * 返回检查异常框架的迭代器
     * 
     * @param array<callable> $frameFilters
     * 
     * @return \Whoops\Exception\FrameCollection
     */
    public function getFrames(array $frameFilters = []);
}
