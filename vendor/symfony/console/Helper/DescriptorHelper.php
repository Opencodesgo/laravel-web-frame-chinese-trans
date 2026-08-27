<?php
/**
 * Symfony，Component，Console，助手，描述符助手
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper;

use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Descriptor\JsonDescriptor;
use Symfony\Component\Console\Descriptor\MarkdownDescriptor;
use Symfony\Component\Console\Descriptor\ReStructuredTextDescriptor;
use Symfony\Component\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Descriptor\XmlDescriptor;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class adds helper method to describe objects in various formats.
 * 这个类添加了帮助器方法来描述各种格式的对象。
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class DescriptorHelper extends Helper
{
    /**
     * @var DescriptorInterface[]
     */
    private array $descriptors = [];

    public function __construct()
    {
        $this
            ->register('txt', new TextDescriptor())
            ->register('xml', new XmlDescriptor())
            ->register('json', new JsonDescriptor())
            ->register('md', new MarkdownDescriptor())
            ->register('rst', new ReStructuredTextDescriptor())
        ;
    }

    /**
     * Describes an object if supported.
	 * 描述对象（如果支持）
     *
     * Available options are:
     * * format: string, the output format name
     * * raw_text: boolean, sets output type as raw
     *
     * @return void
     *
     * @throws InvalidArgumentException when the given format is not supported
     */
    public function describe(OutputInterface $output, ?object $object, array $options = [])
    {
        $options = array_merge([
            'raw_text' => false,
            'format' => 'txt',
        ], $options);

        if (!isset($this->descriptors[$options['format']])) {
            throw new InvalidArgumentException(\sprintf('Unsupported format "%s".', $options['format']));
        }

        $descriptor = $this->descriptors[$options['format']];
        $descriptor->describe($output, $object, $options);
    }

    /**
     * Registers a descriptor.
	 * 注册一个描述符
     *
     * @return $this
     */
    public function register(string $format, DescriptorInterface $descriptor): static
    {
        $this->descriptors[$format] = $descriptor;

        return $this;
    }

    public function getName(): string
    {
        return 'descriptor';
    }

    public function getFormats(): array
    {
        return array_keys($this->descriptors);
    }
}
