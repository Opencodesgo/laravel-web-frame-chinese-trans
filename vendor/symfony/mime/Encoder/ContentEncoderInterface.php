<?php
/**
 * Symfony，Component，Mime，译码器，内容编码器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Encoder;

/**
 * @author Chris Corbyn
 */
interface ContentEncoderInterface extends EncoderInterface
{
    /**
     * Encodes the stream to a Generator.
	 * 将流编码为生成器
     *
     * @param resource $stream
     */
    public function encodeByteStream($stream, int $maxLineLength = 0): iterable;

    /**
     * Gets the MIME name of this content encoding scheme.
	 * 获取此内容编码模式的MIME名称
     */
    public function getName(): string;
}
