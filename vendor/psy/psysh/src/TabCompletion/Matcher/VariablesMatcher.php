<?php
/**
 * Psy，选项卡完成，匹配程序，变量匹配器
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

/**
 * A variable name tab completion Matcher.
 * 一个变量名选项卡完成匹配器。
 *
 * This matcher provides completion for variable names in the current Context.
 *
 * @author Marc Garcia <markcial@gmail.com>
 */
class VariablesMatcher extends AbstractContextAwareMatcher
{
    /**
     * {@inheritdoc}
     */
    public function getMatches(array $tokens, array $info = []): array
    {
        $var = \str_replace('$', '', $this->getInput($tokens));

        return \array_filter(\array_keys($this->getVariables()), function ($variable) use ($var) {
            return AbstractMatcher::startsWith($var, $variable);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function hasMatched(array $tokens): bool
    {
        $token = \array_pop($tokens);

        switch (true) {
            case self::hasToken([self::T_OPEN_TAG, self::T_VARIABLE], $token):
            case \is_string($token) && $token === '$':
            case self::isOperator($token):
                return true;
        }

        return false;
    }
}
