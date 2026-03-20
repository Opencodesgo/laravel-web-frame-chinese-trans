<?php

/**
 * League，Flysystem，Adapter，可以覆盖文件
 */


namespace League\Flysystem\Adapter;

/**
 * Adapters that implement this interface let the Filesystem know that files can be overwritten using the write
 * functions and don't need the update function to be called. This can help improve performance when asserts are disabled.
 * 实现这个接口的适配器让Filesystem知道文件可以被覆盖。
 */
interface CanOverwriteFiles
{
}
