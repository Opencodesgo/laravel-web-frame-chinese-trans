<?php
/**
 * Symfony，Component，Mime，标题，标头接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Header;

/**
 * A MIME Header.
 * MIME标头
 *
 * @author Chris Corbyn
 */
interface HeaderInterface
{
    /**
     * Sets the body.
	 * 设置主体
     *
     * The type depends on the Header concrete class.
     *
     * @return void
     */
    public function setBody(mixed $body);

    /**
     * Gets the body.
	 * 得到主体
     *
     * The return type depends on the Header concrete class.
     */
    public function getBody(): mixed;

    /**
     * @return void
     */
    public function setCharset(string $charset);

    public function getCharset(): ?string;

    /**
     * @return void
     */
    public function setLanguage(string $lang);

    public function getLanguage(): ?string;

    public function getName(): string;

    /**
     * @return void
     */
    public function setMaxLineLength(int $lineLength);

    public function getMaxLineLength(): int;

    /**
     * Gets this Header rendered as a compliant string.
	 * 获取显示为兼容字符串的此标头
     */
    public function toString(): string;

    /**
     * Gets the header's body, prepared for folding into a final header value.
	 * 获取标头的主体，准备折叠为最终标头值。
     *
     * This is not necessarily RFC 2822 compliant since folding white space is
     * not added at this stage (see {@link toString()} for that).
     */
    public function getBodyAsString(): string;
}
