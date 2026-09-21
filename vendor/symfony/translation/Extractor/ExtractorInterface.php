<?php
/**
 * Symfony，Component，Translation，提取器，提取器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Extractor;

use Symfony\Component\Translation\MessageCatalogue;

/**
 * Extracts translation messages from a directory or files to the catalogue.
 * New found messages are injected to the catalogue using the prefix.
 * 从目录或文件中提取翻译消息并导入到词典中。新发现的消息将通过前缀注入到词典中。
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
interface ExtractorInterface
{
    /**
     * Extracts translation messages from files, a file or a directory to the catalogue.
	 * 将翻译消息从文件、文件或目录提取到目录。
     *
     * @param string|iterable<string> $resource Files, a file or a directory
     *
     * @return void
     */
    public function extract(string|iterable $resource, MessageCatalogue $catalogue);

    /**
     * Sets the prefix that should be used for new found messages.
	 * 设置应用于新发现消息的前缀
     *
     * @return void
     */
    public function setPrefix(string $prefix);
}
