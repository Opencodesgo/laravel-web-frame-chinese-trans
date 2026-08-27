<?php
/**
 * Symfony，Component，HttpFoundation，请求匹配器，方案请求匹配器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Checks the HTTP scheme of a Request.
 * 检查请求的HTTP方案
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SchemeRequestMatcher implements RequestMatcherInterface
{
    /**
     * @var string[]
     */
    private array $schemes;

    /**
     * @param string[]|string $schemes A scheme or a list of schemes
     *                                 Strings can contain a comma-delimited list of schemes
     */
    public function __construct(array|string $schemes)
    {
        $this->schemes = array_reduce(array_map('strtolower', (array) $schemes), static fn (array $schemes, string $scheme) => array_merge($schemes, preg_split('/\s*,\s*/', $scheme)), []);
    }

    public function matches(Request $request): bool
    {
        if (!$this->schemes) {
            return true;
        }

        return \in_array($request->getScheme(), $this->schemes, true);
    }
}
