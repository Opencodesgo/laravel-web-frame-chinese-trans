<?php
/**
 * Symfony，Component，Translation，提取器，链提取器
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
 * 链提取器从模板文件中提取翻译消息。
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class ChainExtractor implements ExtractorInterface
{
    /**
     * The extractors.
	 * 萃取器
     *
     * @var ExtractorInterface[]
     */
    private $extractors = [];

    /**
     * Adds a loader to the translation extractor.
     */
    public function addExtractor(string $format, ExtractorInterface $extractor)
    {
        $this->extractors[$format] = $extractor;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix(string $prefix)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setPrefix($prefix);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->extract($directory, $catalogue);
        }
    }
}
