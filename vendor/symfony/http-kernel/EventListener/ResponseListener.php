<?php
/**
 * Symfony，Component，HttpKernel，事件监听器，响应监听器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ResponseListener fixes the Response headers based on the Request.
 * ResponseListener 根据请求修复响应头
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class ResponseListener implements EventSubscriberInterface
{
    private $charset;
    private $addContentLanguageHeader;

    public function __construct(string $charset, bool $addContentLanguageHeader = false)
    {
        $this->charset = $charset;
        $this->addContentLanguageHeader = $addContentLanguageHeader;
    }

    /**
     * Filters the Response.
	 * 过滤响应
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        if (null === $response->getCharset()) {
            $response->setCharset($this->charset);
        }

        if ($this->addContentLanguageHeader && !$response->isInformational() && !$response->isEmpty() && !$response->headers->has('Content-Language')) {
            $response->headers->set('Content-Language', $event->getRequest()->getLocale());
        }

        if ($event->getRequest()->attributes->get('_vary_by_language')) {
            $response->setVary('Accept-Language', false);
        }

        $response->prepare($event->getRequest());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
