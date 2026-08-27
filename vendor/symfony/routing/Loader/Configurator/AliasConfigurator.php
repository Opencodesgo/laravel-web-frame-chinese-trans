<?php
/**
 * Symfony，Component，Routing，加载器，配置，别名配置器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Loader\Configurator;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Alias;

class AliasConfigurator
{
    private Alias $alias;

    public function __construct(Alias $alias)
    {
        $this->alias = $alias;
    }

    /**
     * Whether this alias is deprecated, that means it should not be called anymore.
	 * 无论该别名是否已弃用，都意味着不应再调用它。
     *
     * @param string $package The name of the composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The deprecation message to use
     *
     * @return $this
     *
     * @throws InvalidArgumentException when the message template is invalid
     */
    public function deprecate(string $package, string $version, string $message): static
    {
        $this->alias->setDeprecated($package, $version, $message);

        return $this;
    }
}
