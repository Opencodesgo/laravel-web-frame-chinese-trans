<?php
/**
 * Symfony，Component，Mime，译码器，Mime 报头编码器接口
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
interface MimeHeaderEncoderInterface
{
    /**
     * Get the MIME name of this content encoding scheme.
	 * 获取此内容编码方案的MIME名称
     */
    public function getName(): string;
}
