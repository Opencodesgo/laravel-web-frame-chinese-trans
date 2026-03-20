<?php
/**
 * Psr，Http，客户端，客户端异常接口
 */

namespace Psr\Http\Client;

/**
 * Every HTTP client related exception MUST implement this interface.
 * 每个HTTP客户端相关的异常都必须实现这个接口
 */
interface ClientExceptionInterface extends \Throwable
{
}
