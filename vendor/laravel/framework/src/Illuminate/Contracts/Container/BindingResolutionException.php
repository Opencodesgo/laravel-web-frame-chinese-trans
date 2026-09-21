<?php
/**
 * Illuminate，契约，容器，绑定解析异常
 */

namespace Illuminate\Contracts\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class BindingResolutionException extends Exception implements ContainerExceptionInterface
{
    //
}
