<?php
/**
 * Termwind，异常，风格未找到
 */

declare(strict_types=1);

namespace Termwind\Exceptions;

use InvalidArgumentException;

/**
 * @internal
 */
final class StyleNotFound extends InvalidArgumentException
{
    /**
     * Creates a new style not found instance.
	 * 创建一个新的未找到样式实例
     */
    private function __construct(string $message)
    {
        parent::__construct($message, 0, $this->getPrevious());
    }

    /**
     * Creates a new style not found instance from the given style.
	 * 从给定的样式创建一个新的未找到的样式实例
     */
    public static function fromStyle(string $style): self
    {
        return new self(sprintf('Style [%s] not found.', $style));
    }
}
