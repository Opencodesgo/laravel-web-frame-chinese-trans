<?php
/**
 * Symfony，Component，Translation，提取器，链式提取器
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
 * ChainExtractor extracts translation messages from template files.
 * ChainExtractor从模板文件中提取翻译消息。
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class ChainExtractor implements ExtractorInterface
{
    /**
     * The extractors.
	 * 提取器
     *
     * @var ExtractorInterface[]
     */
    private array $extractors = [];

    /**
     * Adds a loader to the translation extractor.
	 * 向翻译提取器添加加载器
     *
     * @return void
     */
    public function addExtractor(string $format, ExtractorInterface $extractor)
    {
        $this->extractors[$format] = $extractor;
    }

    /**
     * @return void
     */
    public function setPrefix(string $prefix)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setPrefix($prefix);
        }
    }

    /**
     * @return void
     */
    public function extract(string|iterable $directory, MessageCatalogue $catalogue)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->extract($directory, $catalogue);
        }
    }
}
