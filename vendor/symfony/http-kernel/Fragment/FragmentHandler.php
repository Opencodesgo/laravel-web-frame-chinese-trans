<?php
/**
 * Symfony，Component，HttpKernel，片段，片段处理程序
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Renders a URI that represents a resource fragment.
 * 呈现表示资源片段的URI。
 *
 * This class handles the rendering of resource fragments that are included into
 * a main resource. The handling of the rendering is managed by specialized renderers.
 * 该类负责处理被包含在主资源中的资源片段的渲染。渲染的处理由专门的渲染器来管理。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @see FragmentRendererInterface
 */
class FragmentHandler
{
    private bool $debug;
    private array $renderers = [];
    private RequestStack $requestStack;

    /**
     * @param FragmentRendererInterface[] $renderers An array of FragmentRendererInterface instances
     * @param bool                        $debug     Whether the debug mode is enabled or not
     */
    public function __construct(RequestStack $requestStack, array $renderers = [], bool $debug = false)
    {
        $this->requestStack = $requestStack;
        foreach ($renderers as $renderer) {
            $this->addRenderer($renderer);
        }
        $this->debug = $debug;
    }

    /**
     * Adds a renderer.
	 * 添加渲染器
     *
     * @return void
     */
    public function addRenderer(FragmentRendererInterface $renderer)
    {
        $this->renderers[$renderer->getName()] = $renderer;
    }

    /**
     * Renders a URI and returns the Response content.
	 * 呈现一个URI并返回响应内容。
     *
     * Available options:
     *
     *  * ignore_errors: true to return an empty string in case of an error
     *
     * @throws \InvalidArgumentException when the renderer does not exist
     * @throws \LogicException           when no main request is being handled
     */
    public function render(string|ControllerReference $uri, string $renderer = 'inline', array $options = []): ?string
    {
        if (!isset($options['ignore_errors'])) {
            $options['ignore_errors'] = !$this->debug;
        }

        if (!isset($this->renderers[$renderer])) {
            throw new \InvalidArgumentException(\sprintf('The "%s" renderer does not exist.', $renderer));
        }

        if (!$request = $this->requestStack->getCurrentRequest()) {
            throw new \LogicException('Rendering a fragment can only be done when handling a Request.');
        }

        return $this->deliver($this->renderers[$renderer]->render($uri, $request, $options));
    }

    /**
     * Delivers the Response as a string.
	 * 将响应作为字符串传递。
     *
     * When the Response is a StreamedResponse, the content is streamed immediately
     * instead of being returned.
	 * 当响应为流式响应时，内容会立即被流式传输，而不是返回。
     *
     * @return string|null The Response content or null when the Response is streamed
     *
     * @throws \RuntimeException when the Response is not successful
     */
    protected function deliver(Response $response): ?string
    {
        if (!$response->isSuccessful()) {
            $responseStatusCode = $response->getStatusCode();
            throw new \RuntimeException(\sprintf('Error when rendering "%s" (Status code is %d).', $this->requestStack->getCurrentRequest()->getUri(), $responseStatusCode), 0, new HttpException($responseStatusCode));
        }

        if (!$response instanceof StreamedResponse) {
            return $response->getContent();
        }

        $response->sendContent();

        return null;
    }
}
