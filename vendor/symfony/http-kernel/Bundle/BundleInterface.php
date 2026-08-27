<?php
/**
 * Symfony，Component，HttpKernel，Bundle，Bundle 接口 
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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * BundleInterface.
 * 包的接口
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface BundleInterface
{
    /**
     * Boots the Bundle.
	 * 启动Bundle
     *
     * @return void
     */
    public function boot();

    /**
     * Shutdowns the Bundle.
	 * 关闭Bundle
     *
     * @return void
     */
    public function shutdown();

    /**
     * Builds the bundle.
	 * 构建包
     *
     * It is only ever called once when the cache is empty.
     *
     * @return void
     */
    public function build(ContainerBuilder $container);

    /**
     * Returns the container extension that should be implicitly loaded.
	 * 返回应该隐式加载的容器扩展
     */
    public function getContainerExtension(): ?ExtensionInterface;

    /**
     * Returns the bundle name (the class short name).
	 * 返回包名（类的短名称）
     */
    public function getName(): string;

    /**
     * Gets the Bundle namespace.
	 * 获取Bundle命名空间
     */
    public function getNamespace(): string;

    /**
     * Gets the Bundle directory path.
	 * 获取Bundle目录路径
     *
     * The path should always be returned as a Unix path (with /).
     */
    public function getPath(): string;

    /**
     * @return void
     */
    public function setContainer(?ContainerInterface $container);
}
