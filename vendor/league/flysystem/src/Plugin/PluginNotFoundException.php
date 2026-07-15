<?php
/**
 * League，Flysystem，插件，插件未发现异常
 */

namespace League\Flysystem\Plugin;

use LogicException;

class PluginNotFoundException extends LogicException
{
    // This exception doesn't require additional information.
}
