<?php
/**
 * Symfony，Component，Mime，译码器，Base64 内容编码器
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
final class Base64MimeHeaderEncoder extends Base64Encoder implements MimeHeaderEncoderInterface
{
    public function getName(): string
    {
        return 'B';
    }

    /**
     * Takes an unencoded string and produces a Base64 encoded string from it.
	 * 获取一个未编码的字符串，并从中生成一个Base64编码的字符串。
     *
     * If the charset is iso-2022-jp, it uses mb_encode_mimeheader instead of
     * default encodeString, otherwise pass to the parent method.
     */
    public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string
    {
        if ('iso-2022-jp' === strtolower($charset)) {
            $old = mb_internal_encoding();
            mb_internal_encoding('utf-8');
            $newstring = mb_encode_mimeheader($string, 'iso-2022-jp', $this->getName(), "\r\n");
            mb_internal_encoding($old);

            return $newstring;
        }

        return parent::encodeString($string, $charset, $firstLineOffset, $maxLineLength);
    }
}
