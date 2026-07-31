<?php
/**
 * Symfony，Component，Console，助手，辅助接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper;

/**
 * HelperInterface is the interface all helpers must implement.
 * HelperInterface是所有助手必须实现的接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface HelperInterface
{
    /**
     * Sets the helper set associated with this helper.
	 * 设置与此助手关联的助手设置
     */
    public function setHelperSet(?HelperSet $helperSet = null);

    /**
     * Gets the helper set associated with this helper.
	 * 获取与此助手关联的助手设置
     *
     * @return HelperSet|null
     */
    public function getHelperSet();

    /**
     * Returns the canonical name of this helper.
	 * 返回此助手的规范名称
     *
     * @return string
     */
    public function getName();
}
