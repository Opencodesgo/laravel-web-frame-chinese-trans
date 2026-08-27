<?php
/**
 * DeepCopy，过滤器，可链接过滤器
 */

namespace DeepCopy\Filter;

/**
 * Defines a decorator filter that will not stop the chain of filters.
 * 定义一个不会停止过滤器链的装饰器过滤器
 */
class ChainableFilter implements Filter
{
    /**
     * @var Filter
     */
    protected $filter;

    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    public function apply($object, $property, $objectCopier)
    {
        $this->filter->apply($object, $property, $objectCopier);
    }
}
