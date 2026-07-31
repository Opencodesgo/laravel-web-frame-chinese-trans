<?php
/**
 * Symfony，Component，HttpKernel，内核接口
 */

/*
 * This file is part of the Symfony package.
 * 该文件是Symfony包的一部分
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * The Kernel is the heart of the Symfony system.
 * 内核是Symfony系统的核心
 *
 * It manages an environment made of application kernel and bundles.
 *
 * @method string getBuildDir() Returns the build directory - not implementing it is deprecated since Symfony 5.2.
 *                              This directory should be used to store build artifacts, and can be read-only at runtime.
 *                              Caches written at runtime should be stored in the "cache directory" ({@see KernelInterface::getCacheDir()}).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface KernelInterface extends HttpKernelInterface
{
    /**
     * Returns an array of bundles to register.
	 * 返回要注册的bundle数组
     *
     * @return iterable<mixed, BundleInterface>
     */
    public function registerBundles();

    /**
     * Loads the container configuration.
	 * 加载容器配置
     */
    public function registerContainerConfiguration(LoaderInterface $loader);

    /**
     * Boots the current kernel.
	 * 引导当前内核
	 * 
     */
    public function boot();

    /**
     * Shutdowns the kernel.
	 * 关闭内核
     *
     * This method is mainly useful when doing functional testing.
     */
    public function shutdown();

    /**
     * Gets the registered bundle instances.
	 * 获取已注册的包实例
     *
     * @return array<string, BundleInterface>
     */
    public function getBundles();

    /**
     * Returns a bundle.
	 * 返回一个bundle
     *
     * @return BundleInterface
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     */
    public function getBundle(string $name);

    /**
     * Returns the file path for a given bundle resource.
	 * 返回给定包资源的文件路径。
     *
     * A Resource can be a file or a directory.
     *
     * The resource name must follow the following pattern:
     *
     *     "@BundleName/path/to/a/file.something"
     *
     * where BundleName is the name of the bundle
     * and the remaining part is the relative path in the bundle.
     *
     * @return string
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe characters
     */
    public function locateResource(string $name);

    /**
     * Gets the environment.
	 * 获取环境
     *
     * @return string
     */
    public function getEnvironment();

    /**
     * Checks if debug mode is enabled.
	 * 检查是否启用了调试模式
     *
     * @return bool
     */
    public function isDebug();

    /**
     * Gets the project dir (path of the project's composer file).
	 * 获取项目目录（项目编写器文件的路径
     *
     * @return string
     */
    public function getProjectDir();

    /**
     * Gets the current container.
	 * 获取当前容器
     *
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * Gets the request start time (not available if debug is disabled).
	 * 获取请求启动时间(如果调试禁用)
     *
     * @return float
     */
    public function getStartTime();

    /**
     * Gets the cache directory.
	 * 获取缓存目录
     *
     * Since Symfony 5.2, the cache directory should be used for caches that are written at runtime.
     * For caches and artifacts that can be warmed at compile-time and deployed as read-only,
     * use the new "build directory" returned by the {@see getBuildDir()} method.
     *
     * @return string
     */
    public function getCacheDir();

    /**
     * Gets the log directory.
	 * 获取日志目录
     *
     * @return string
     */
    public function getLogDir();

    /**
     * Gets the charset of the application.
	 * 获取应用程序的字符集
     *
     * @return string
     */
    public function getCharset();
}
