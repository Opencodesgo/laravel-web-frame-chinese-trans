<?php
/**
 * Symfony，Component，HttpKernel，控制器元数据，元数据工厂接口
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
 * 构建方法参数数据
 *
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
interface ArgumentMetadataFactoryInterface
{
    /**
     * @param string|object|array $controller The controller to resolve the arguments for
	 * 要解析参数的控制器
     *
     * @return ArgumentMetadata[]
     */
    public function createArgumentMetadata($controller);
}
