<?php
/**
 * Psr，Container，容器接口
 */

declare(strict_types=1);

namespace Psr\Container;

/**
 * Describes the interface of a container that exposes methods to read its entries.
 * 描述一个容器的接口,它公开方法读取其条目。
 */
interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
	 * 通过它的标识符找到一个容器的条目并返回它。
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
	 * 如果容器可以返回给定标识符的条目，则返回true。
	 * 否则返回假。
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id);
}
