<?php
/**
 * League，Flysystem，Plugin，插件未发现异常
 */

namespace League\Flysystem\Plugin;

use LogicException;

class PluginNotFoundException extends LogicException
{
    // This exception doesn't require additional information.
	// 此异常不需要额外的信息
}
