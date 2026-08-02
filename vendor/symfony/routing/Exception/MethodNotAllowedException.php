<?php

/*
 * This file is part of the Symfony package.
 * 该文件是Symfony包的一部分
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Exception;

/**
 * The resource was found but the request method is not allowed.
 * 已找到资源，但不允许使用请求方法。
 *
 * This exception should trigger an HTTP 405 response in your application code.
 * 此异常应该在应用程序代码中触发HTTP 405响应。
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class MethodNotAllowedException extends \RuntimeException implements ExceptionInterface
{
    protected $allowedMethods = [];

    /**
     * @param string[] $allowedMethods
     */
    public function __construct(array $allowedMethods, ?string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if (null === $message) {
            trigger_deprecation('symfony/routing', '5.3', 'Passing null as $message to "%s()" is deprecated, pass an empty string instead.', __METHOD__);

            $message = '';
        }

        $this->allowedMethods = array_map('strtoupper', $allowedMethods);

        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets the allowed HTTP methods.
	 * 获取允许的HTTP方法
     *
     * @return string[]
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }
}
