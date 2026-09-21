<?php
/**
 * Psy，选项卡完成，匹配程序，上下文感知匹配器
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\TabCompletion\Matcher;

use Psy\Context;
use Psy\ContextAware;

/**
 * An abstract tab completion Matcher which implements ContextAware.
 * 一个抽象的选项卡补全匹配器，实现了上下文感知。
 *
 * The AutoCompleter service will inject a Context instance into all
 * ContextAware Matchers.
 *
 * @author Marc Garcia <markcial@gmail.com>
 */
abstract class AbstractContextAwareMatcher extends AbstractMatcher implements ContextAware
{
    /**
     * Context instance (for ContextAware interface).
	 * 上下文实例（用于ContextAware接口）
     *
     * @var Context
     */
    protected $context;

    /**
     * ContextAware interface.
	 * ContextAware接口
     *
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get a Context variable by name.
	 * 按名称获取上下文变量
     *
     * @param string $var Variable name
     *
     * @return mixed
     */
    protected function getVariable(string $var)
    {
        return $this->context->get($var);
    }

    /**
     * Get all variables in the current Context.
	 * 获取当前上下文中的所有变量
     *
     * @return array
     */
    protected function getVariables(): array
    {
        return $this->context->getAll();
    }
}
