<?php
/**
 * Illuminate，契约，容器，循环依赖异常
 */

namespace Illuminate\Contracts\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
