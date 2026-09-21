<?php
/**
 * Symfony，Component，Mime，译码器，Qp 内容编码器
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
 * @author Lars Strojny
 */
final class QpContentEncoder implements ContentEncoderInterface
{
    public function encodeByteStream($stream, int $maxLineLength = 0): iterable
    {
        if (!\is_resource($stream)) {
            throw new \TypeError(\sprintf('Method "%s" takes a stream as a first argument.', __METHOD__));
        }

        // we don't use PHP stream filters here as the content should be small enough
		// 这里我们不使用PHP流过滤器，因为内容应该足够小。
        yield $this->encodeString(stream_get_contents($stream), 'utf-8', 0, $maxLineLength);
    }

    public function getName(): string
    {
        return 'quoted-printable';
    }

    public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string
    {
        return $this->standardize(quoted_printable_encode($string));
    }

    /**
     * Make sure CRLF is correct and HT/SPACE are in valid places.
	 * 确保CRLF是正确的，HT/SPACE在有效的地方。
     */
    private function standardize(string $string): string
    {
        // transform CR or LF to CRLF
        $string = preg_replace('~=0D(?!=0A)|(?<!=0D)=0A~', '=0D=0A', $string);
        // transform =0D=0A to CRLF
        $string = str_replace(["\t=0D=0A", ' =0D=0A', '=0D=0A'], ["=09\r\n", "=20\r\n", "\r\n"], $string);

        return match (\ord(substr($string, -1))) {
            0x09 => substr_replace($string, '=09', -1),
            0x20 => substr_replace($string, '=20', -1),
            default => $string,
        };
    }
}
