<?php
/**
 * Symfony，Component，Translation，转存器，文件转储
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Dumper;

use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\Exception\RuntimeException;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * FileDumper is an implementation of DumperInterface that dump a message catalogue to file(s).
 * FileDumper是DumperInterface的实现，它将消息目录转储到文件中。
 *
 * Options:
 * - path (mandatory): the directory where the files should be saved
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
abstract class FileDumper implements DumperInterface
{
    /**
     * A template for the relative paths to files.
	 * 文件的相对路径模板
     *
     * @var string
     */
    protected $relativePathTemplate = '%domain%.%locale%.%extension%';

    /**
     * Sets the template for the relative paths to files.
	 * 为文件的相对路径设置模板
     *
     * @return void
     */
    public function setRelativePathTemplate(string $relativePathTemplate)
    {
        $this->relativePathTemplate = $relativePathTemplate;
    }

    /**
     * @return void
     */
    public function dump(MessageCatalogue $messages, array $options = [])
    {
        if (!\array_key_exists('path', $options)) {
            throw new InvalidArgumentException('The file dumper needs a path option.');
        }

        // save a file for each domain
        foreach ($messages->getDomains() as $domain) {
            $fullpath = $options['path'].'/'.$this->getRelativePath($domain, $messages->getLocale());
            if (!file_exists($fullpath)) {
                $directory = \dirname($fullpath);
                if (!file_exists($directory) && !@mkdir($directory, 0777, true)) {
                    throw new RuntimeException(\sprintf('Unable to create directory "%s".', $directory));
                }
            }

            $intlDomain = $domain.MessageCatalogue::INTL_DOMAIN_SUFFIX;
            $intlMessages = $messages->all($intlDomain);

            if ($intlMessages) {
                $intlPath = $options['path'].'/'.$this->getRelativePath($intlDomain, $messages->getLocale());
                file_put_contents($intlPath, $this->formatCatalogue($messages, $intlDomain, $options));

                $messages->replace([], $intlDomain);

                try {
                    if ($messages->all($domain)) {
                        file_put_contents($fullpath, $this->formatCatalogue($messages, $domain, $options));
                    }
                    continue;
                } finally {
                    $messages->replace($intlMessages, $intlDomain);
                }
            }

            file_put_contents($fullpath, $this->formatCatalogue($messages, $domain, $options));
        }
    }

    /**
     * Transforms a domain of a message catalogue to its string representation.
	 * 将消息目录的域转换为其字符串表示形式
     */
    abstract public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []): string;

    /**
     * Gets the file extension of the dumper.
	 * 获取转储程序的文件扩展名
     */
    abstract protected function getExtension(): string;

    /**
     * Gets the relative file path using the template.
	 * 使用模板获取相对文件路径
     */
    private function getRelativePath(string $domain, string $locale): string
    {
        return strtr($this->relativePathTemplate, [
            '%domain%' => $domain,
            '%locale%' => $locale,
            '%extension%' => $this->getExtension(),
        ]);
    }
}
