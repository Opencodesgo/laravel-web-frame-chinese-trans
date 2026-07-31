<?php
/**
 * DeepCopy，过滤器，主义，主义代理过滤器
 */

namespace DeepCopy\Filter\Doctrine;

use DeepCopy\Filter\Filter;

/**
 * @final
 */
class DoctrineProxyFilter implements Filter
{
    /**
     * Triggers the magic method __load() on a Doctrine Proxy class to load the
     * actual entity from the database.
	 * 触发Doctrine Proxy类上的神奇方法__load()来加载数据库中的实际实体。
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $object->__load();
    }
}
