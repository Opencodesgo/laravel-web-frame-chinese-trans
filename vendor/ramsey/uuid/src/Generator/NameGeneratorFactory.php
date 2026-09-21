<?php
/**
 * Ramsey，Uuid，生成器，名称生成器工厂
 */

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Uuid\Generator;

/**
 * NameGeneratorFactory retrieves a default name generator, based on the environment
 * NameGeneratorFactory 根据环境检索默认名称生成器
 */
class NameGeneratorFactory
{
    /**
     * Returns a default name generator, based on the current environment
	 * 返回基于当前环境的默认名称生成器
     */
    public function getGenerator(): NameGeneratorInterface
    {
        return new DefaultNameGenerator();
    }
}
