<?php
/**
 * Symfony，Component，HttpKernel，属性，捆绑，捆绑接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Bundle;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * BundleInterface.
 * 捆绑接口
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface BundleInterface extends ContainerAwareInterface
{
    /**
     * Boots the Bundle.
	 * 启动包
     */
    public function boot();

    /**
     * Shutdowns the Bundle.
	 * 关闭Bundle
     */
    public function shutdown();

    /**
     * Builds the bundle.
	 * 构建包
     *
     * It is only ever called once when the cache is empty.
	 * 它只在缓存为空时调用一次
     */
    public function build(ContainerBuilder $container);

    /**
     * Returns the container extension that should be implicitly loaded.
	 * 返回应该隐式加载的容器扩展
     *
     * @return ExtensionInterface|null
     */
    public function getContainerExtension();

    /**
     * Returns the bundle name (the class short name).
	 * 返回包名（类的短名称）
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the Bundle namespace.
	 * 得到包命名空间
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Gets the Bundle directory path.
	 * 得到包目录路径
     *
     * The path should always be returned as a Unix path (with /).
     *
     * @return string
     */
    public function getPath();
}
