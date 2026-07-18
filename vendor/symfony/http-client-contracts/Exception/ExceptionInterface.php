<?php
/**
 * Symfony，契约，HTTP客户端，异常，异常接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Contracts\HttpClient\Exception;

/**
 * The base interface for all exceptions in the contract.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface ExceptionInterface extends \Throwable
{
}
