<?php
/**
 * Dotenv，存储库，适配器，阅读器接口
 */

namespace Dotenv\Repository\Adapter;

interface ReaderInterface extends AvailabilityInterface
{
    /**
     * Get an environment variable, if it exists.
     *
     * @param non-empty-string $name
     *
     * @return \PhpOption\Option<string|null>
     */
    public function get($name);
}
