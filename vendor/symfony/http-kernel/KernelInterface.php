<?php
/**
 * Symfony，Component，HttpKernel，内核接口
 */

/*
 * This file is part of the Symfony package.
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
 * 内核是Symfony系统的核心。
 *
 * It manages an environment made of application kernel and bundles.
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
    public function registerBundles(): iterable;

    /**
     * Loads the container configuration.
	 * 加载容器配置
     *
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader);

    /**
     * Boots the current kernel.
	 * 引导当前内核
     *
     * @return void
     */
    public function boot();

    /**
     * Shutdowns the kernel.
	 * 关闭内核。
     *
     * This method is mainly useful when doing functional testing.
	 * 这种方法主要在进行功能测试时很有用。
     *
     * @return void
     */
    public function shutdown();

    /**
     * Gets the registered bundle instances.
	 * 获取已注册的包实例
     *
     * @return array<string, BundleInterface>
     */
    public function getBundles(): array;

    /**
     * Returns a bundle.
	 * 返回一个bundle
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     */
    public function getBundle(string $name): BundleInterface;

    /**
     * Returns the file path for a given bundle resource.
	 * 返回给定bundle资源的文件路径。
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
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe characters
     */
    public function locateResource(string $name): string;

    /**
     * Gets the environment.
	 * 获取环境
     */
    public function getEnvironment(): string;

    /**
     * Checks if debug mode is enabled.
	 * 检查是否启用了调试模式
     */
    public function isDebug(): bool;

    /**
     * Gets the project dir (path of the project's composer file).
	 * 获取项目目录（项目编写器文件的路径）
     */
    public function getProjectDir(): string;

    /**
     * Gets the current container.
	 * 获取当前容器
     */
    public function getContainer(): ContainerInterface;

    /**
     * Gets the request start time (not available if debug is disabled).
	 * 获取请求开始时间（如果禁用调试则不可用）
     */
    public function getStartTime(): float;

    /**
     * Gets the cache directory.
	 * 获取缓存目录。
     *
     * Since Symfony 5.2, the cache directory should be used for caches that are written at runtime.
     * For caches and artifacts that can be warmed at compile-time and deployed as read-only,
     * use the new "build directory" returned by the {@see getBuildDir()} method.
     */
    public function getCacheDir(): string;

    /**
     * Returns the build directory.
	 * 返回生成目录。
     *
     * This directory should be used to store build artifacts, and can be read-only at runtime.
     * Caches written at runtime should be stored in the "cache directory" ({@see KernelInterface::getCacheDir()}).
     */
    public function getBuildDir(): string;

    /**
     * Gets the log directory.
	 * 得到日志目录
     */
    public function getLogDir(): string;

    /**
     * Gets the charset of the application.
	 * 获取应用程序的字符集
     */
    public function getCharset(): string;
}
