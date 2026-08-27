<?php
/**
 * Symfony，Component，Translation，转存器，Csv 文件转储
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

use Symfony\Component\Translation\MessageCatalogue;

/**
 * CsvFileDumper generates a csv formatted string representation of a message catalogue.
 * CsvFileDumper 生成消息目录的csv格式字符串表示形式。
 *
 * @author Stealth35
 */
class CsvFileDumper extends FileDumper
{
    private string $delimiter = ';';
    private string $enclosure = '"';

    public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []): string
    {
        $handle = fopen('php://memory', 'r+');

        foreach ($messages->all($domain) as $source => $target) {
            fputcsv($handle, [$source, $target], $this->delimiter, $this->enclosure, '\\');
        }

        rewind($handle);
        $output = stream_get_contents($handle);
        fclose($handle);

        return $output;
    }

    /**
     * Sets the delimiter and escape character for CSV.
	 * 设置CSV的分隔符和转义字符
     *
     * @return void
     */
    public function setCsvControl(string $delimiter = ';', string $enclosure = '"')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }

    protected function getExtension(): string
    {
        return 'csv';
    }
}
