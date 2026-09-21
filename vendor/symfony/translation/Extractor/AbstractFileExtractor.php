<?php
/**
 * Symfony，Component，Translation，提取器，访问者，抽象文件提取器
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

use Symfony\Component\Translation\Exception\InvalidArgumentException;

/**
 * Base class used by classes that extract translation messages from files.
 * 由从文件中提取翻译消息的类使用的基类。
 *
 * @author Marcos D. Sánchez <marcosdsanchez@gmail.com>
 */
abstract class AbstractFileExtractor
{
    protected function extractFiles(string|iterable $resource): iterable
    {
        if (is_iterable($resource)) {
            $files = [];
            foreach ($resource as $file) {
                if ($this->canBeExtracted($file)) {
                    $files[] = $this->toSplFileInfo($file);
                }
            }
        } elseif (is_file($resource)) {
            $files = $this->canBeExtracted($resource) ? [$this->toSplFileInfo($resource)] : [];
        } else {
            $files = $this->extractFromDirectory($resource);
        }

        return $files;
    }

    private function toSplFileInfo(string $file): \SplFileInfo
    {
        return new \SplFileInfo($file);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function isFile(string $file): bool
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(\sprintf('The "%s" file does not exist.', $file));
        }

        return true;
    }

    /**
     * @return bool
     */
    abstract protected function canBeExtracted(string $file);

    /**
     * @return iterable
     */
    abstract protected function extractFromDirectory(string|array $resource);
}
