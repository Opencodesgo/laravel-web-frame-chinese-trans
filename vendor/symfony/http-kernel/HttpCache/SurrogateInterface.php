<?php
/**
 * Symfony，Component，HttpKernel，HTTP缓存，代理接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface SurrogateInterface
{
    /**
     * Returns surrogate name.
	 * 返回代理名称
     *
     * @return string
     */
    public function getName();

    /**
     * Returns a new cache strategy instance.
	 * 返回一个新的缓存策略实例
     *
     * @return ResponseCacheStrategyInterface
     */
    public function createCacheStrategy();

    /**
     * Checks that at least one surrogate has Surrogate capability.
	 * 检查是否至少有一个代理具有代理能力
     *
     * @return bool
     */
    public function hasSurrogateCapability(Request $request);

    /**
     * Adds Surrogate-capability to the given Request.
	 * 向给定请求添加代理功能
     */
    public function addSurrogateCapability(Request $request);

    /**
     * Adds HTTP headers to specify that the Response needs to be parsed for Surrogate.
	 * 添加HTTP标头，以指定需要为代理解析响应。
     *
     * This method only adds an Surrogate HTTP header if the Response has some Surrogate tags.
     */
    public function addSurrogateControl(Response $response);

    /**
     * Checks that the Response needs to be parsed for Surrogate tags.
	 * 检查是否需要为代理标记解析响应
     *
     * @return bool
     */
    public function needsParsing(Response $response);

    /**
     * Renders a Surrogate tag.
	 * 呈现代理标记
     *
     * @param string|null $alt     An alternate URI
     * @param string      $comment A comment to add as an esi:include tag
     *
     * @return string
     */
    public function renderIncludeTag(string $uri, ?string $alt = null, bool $ignoreErrors = true, string $comment = '');

    /**
     * Replaces a Response Surrogate tags with the included resource content.
	 * 用包含的资源内容替换响应代理标记
     *
     * @return Response
     */
    public function process(Request $request, Response $response);

    /**
     * Handles a Surrogate from the cache.
	 * 处理缓存中的代理
     *
     * @param string $alt An alternative URI
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function handle(HttpCache $cache, string $uri, string $alt, bool $ignoreErrors);
}
