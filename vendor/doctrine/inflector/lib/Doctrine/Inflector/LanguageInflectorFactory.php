<?php
/**
 * Doctrine，Inflector，语言影响器工厂
 */

declare(strict_types=1);

namespace Doctrine\Inflector;

use Doctrine\Inflector\Rules\Ruleset;

interface LanguageInflectorFactory
{
    /**
     * Applies custom rules for singularisation
	 * 为单数应用自定义规则
     *
     * @param bool $reset If true, will unset default inflections for all new rules
     *
     * @return $this
     */
    public function withSingularRules(?Ruleset $singularRules, bool $reset = false): self;

    /**
     * Applies custom rules for pluralisation
	 * 应用自定义规则
     *
     * @param bool $reset If true, will unset default inflections for all new rules
     *
     * @return $this
     */
    public function withPluralRules(?Ruleset $pluralRules, bool $reset = false): self;

    /**
     * Builds the inflector instance with all applicable rules
	 * 用所有适用的规则构建发型器实例
     */
    public function build(): Inflector;
}
