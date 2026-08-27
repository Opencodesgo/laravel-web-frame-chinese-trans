<?php
/**
 * Illuminate，Http，问题，可以预知
 */

namespace Illuminate\Http\Concerns;

use Illuminate\Support\Collection;

trait CanBePrecognitive
{
    /**
     * Filter the given array of rules into an array of rules that are included in precognitive headers.
	 * 将给定的规则数组筛选为包含在预知头中的规则数组
     *
     * @param  array  $rules
     * @return array
     */
    public function filterPrecognitiveRules($rules)
    {
        if (! $this->headers->has('Precognition-Validate-Only')) {
            return $rules;
        }

        return Collection::make($rules)
            ->only(explode(',', $this->header('Precognition-Validate-Only')))
            ->all();
    }

    /**
     * Determine if the request is attempting to be precognitive.
	 * 确定请求是否试图预知
     *
     * @return bool
     */
    public function isAttemptingPrecognition()
    {
        return $this->header('Precognition') === 'true';
    }

    /**
     * Determine if the request is precognitive.
	 * 确定请求是否是预知的。
     *
     * @return bool
     */
    public function isPrecognitive()
    {
        return $this->attributes->get('precognitive', false);
    }
}
