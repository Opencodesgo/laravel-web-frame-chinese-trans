<?php
/**
 * Dotenv，资源库，适配器，适配器接口
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

interface AdapterInterface extends ReaderInterface, WriterInterface
{
    /**
     * Create a new instance of the adapter, if it is available.
	 * 创建适配器的新实例（如果可用）。
     *
     * @return \PhpOption\Option<\Dotenv\Repository\Adapter\AdapterInterface>
     */
    public static function create();
}
