<?php
/**
 * Symfony，Component，Routing，匹配器，编译Url匹配器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Matcher;

use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherTrait;
use Symfony\Component\Routing\RequestContext;

/**
 * Matches URLs based on rules dumped by CompiledUrlMatcherDumper.
 * 根据CompiledUrlMatcherDumper转储的规则匹配url。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class CompiledUrlMatcher extends UrlMatcher
{
    use CompiledUrlMatcherTrait;

    public function __construct(array $compiledRoutes, RequestContext $context)
    {
        $this->context = $context;
        [$this->matchHost, $this->staticRoutes, $this->regexpList, $this->dynamicRoutes, $this->checkCondition] = $compiledRoutes;
    }
}
