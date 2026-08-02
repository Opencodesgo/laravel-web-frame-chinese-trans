<?php
/**
 * League，CommonMark，Util，配置感知接口
 */

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Util;

/**
 * Implement this class to inject the configuration where needed
 * 实现这个类以在需要的地方注入配置
 */
interface ConfigurationAwareInterface
{
    /**
     * @param ConfigurationInterface $configuration
     *
     * @return void
     */
    public function setConfiguration(ConfigurationInterface $configuration);
}
