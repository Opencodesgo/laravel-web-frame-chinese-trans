<?php
/**
 * Symfony，Component，Console，助手，助手接口
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
 * HelperInterface是所有helper必须实现的接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface HelperInterface
{
    /**
     * Sets the helper set associated with this helper.
	 * 设置与此helper关联的helper集
     *
     * @return void
     */
    public function setHelperSet(?HelperSet $helperSet);

    /**
     * Gets the helper set associated with this helper.
	 * 获取与此帮助器关联的帮助器集
     */
    public function getHelperSet(): ?HelperSet;

    /**
     * Returns the canonical name of this helper.
	 * 返回此帮助器的规范名称
     *
     * @return string
     */
    public function getName();
}
