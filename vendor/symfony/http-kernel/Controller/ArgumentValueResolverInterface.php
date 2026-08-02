<?php
/**
 * Symfony，Component，HttpKernel，控制器，参数值解析器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Responsible for resolving the value of an argument based on its metadata.
 * 负责根据参数的元数据解析参数的值
 *
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
interface ArgumentValueResolverInterface
{
    /**
     * Whether this resolver can resolve the value for the given ArgumentMetadata.
	 * 此解析器是否可以解析给定ArgumentMetadata的值
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument);

    /**
     * Returns the possible value(s).
	 * 返回可能的值
     *
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument);
}
