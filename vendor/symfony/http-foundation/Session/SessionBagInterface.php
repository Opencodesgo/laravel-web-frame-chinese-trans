<?php
/**
 * Symfony，Component，HttpFoundation，Session会话，会话包接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session;

/**
 * Session Bag store.
 * 会话包存储
 *
 * @author Drak <drak@zikula.org>
 */
interface SessionBagInterface
{
    /**
     * Gets this bag's name.
	 * 得到包名
     *
     * @return string
     */
    public function getName();

    /**
     * Initializes the Bag.
	 * 初始化Bag
     */
    public function initialize(array &$array);

    /**
     * Gets the storage key for this bag.
	 * 获取这个包的存储钥匙
     *
     * @return string
     */
    public function getStorageKey();

    /**
     * Clears out data from bag.
	 * 清除包中的数据
     *
     * @return mixed Whatever data was contained
     */
    public function clear();
}
