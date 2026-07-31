<?php
/**
 * Symfony，Component，Routing，加载器，Glob 文件装入器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * GlobFileLoader loads files from a glob pattern.
 * GlobFileLoader从glob模式加载文件。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class GlobFileLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, ?string $type = null)
    {
        $collection = new RouteCollection();

        foreach ($this->glob($resource, false, $globResource) as $path => $info) {
            $collection->addCollection($this->import($path));
        }

        $collection->addResource($globResource);

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, ?string $type = null)
    {
        return 'glob' === $type;
    }
}
