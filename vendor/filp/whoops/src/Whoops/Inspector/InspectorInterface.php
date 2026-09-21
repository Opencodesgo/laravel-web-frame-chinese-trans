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
	 * 返回与底层错误相关的php手册的url -当可用时
     *
     * @return string|null
     */
    public function getExceptionDocrefUrl();

    /**
     * Does the wrapped Exception has a previous Exception?
	 * 包装的异常是否有先前的异常？
     * @return bool
     */
    public function hasPreviousException();

    /**
     * Returns an Inspector for a previous Exception, if any.
	 * 返回先前异常的检查器（如果有的话）
     * @todo   Clean this up a bit, cache stuff a bit better.
     * @return InspectorInterface
     */
    public function getPreviousExceptionInspector();

    /**
     * Returns an array of all previous exceptions for this inspector's exception
	 * 返回此检查器异常的所有先前异常的数组
     * @return \Throwable[]
     */
    public function getPreviousExceptions();

    /**
     * Returns an iterator for the inspected exception's
     * frames.
	 * 返回被检查异常框架的迭代器
     * 
     * @param array<callable> $frameFilters
     * 
     * @return \Whoops\Exception\FrameCollection
     */
    public function getFrames(array $frameFilters = []);
}
