<?php
/**
 * Symfony，Component，Translation，转储，转储接口
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
 * DumperInterface is the interface implemented by all translation dumpers.
 * There is no common option.
 * DumperInterface是由所有翻译dumpers实现的接口。
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
interface DumperInterface
{
    /**
     * Dumps the message catalogue.
	 * 转储信息目录
     *
     * @param array $options Options that are used by the dumper
     */
    public function dump(MessageCatalogue $messages, array $options = []);
}
