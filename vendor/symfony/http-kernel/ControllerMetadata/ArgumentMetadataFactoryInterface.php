<?php
/**
 * Symfony，Component，HttpKernel，控制器的元数据，参数元数据工厂接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\ControllerMetadata;

/**
 * Builds method argument data.
 * 构建方法参数数据。
 *
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
interface ArgumentMetadataFactoryInterface
{
    /**
     * @param \ReflectionFunctionAbstract|null $reflector
     *
     * @return ArgumentMetadata[]
     */
    public function createArgumentMetadata(string|object|array $controller/* , \ReflectionFunctionAbstract $reflector = null */): array;
}
