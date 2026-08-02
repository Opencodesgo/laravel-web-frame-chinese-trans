<?php
/**
 * Psr，Container，容器接口
 */

declare(strict_types=1);

namespace Psr\Container;

/**
 * Describes the interface of a container that exposes methods to read its entries.
 * 描述容器的接口，该接口公开用于读取其项的方法。
 */
interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
	 * 根据容器的标识符查找它的条目并返回它。
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
	 * 如果容器可以返回指定标识符的条目，则返回 true；否则返回 false。
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
