<?php
/**
 * League，Flysystem，Adapter，Polyfill，不支持可见性特性
 */

namespace League\Flysystem\Adapter\Polyfill;

use LogicException;

trait NotSupportingVisibilityTrait
{
    /**
     * Get the visibility of a file.
	 * 获得文件的可见性
     *
     * @param string $path
     *
     * @throws LogicException
     */
    public function getVisibility($path)
    {
        throw new LogicException(get_class($this) . ' does not support visibility. Path: ' . $path);
    }

    /**
     * Set the visibility for a file.
	 * 设置文件的可见性
     *
     * @param string $path
     * @param string $visibility
     *
     * @throws LogicException
     */
    public function setVisibility($path, $visibility)
    {
        throw new LogicException(get_class($this) . ' does not support visibility. Path: ' . $path . ', visibility: ' . $visibility);
    }
}
