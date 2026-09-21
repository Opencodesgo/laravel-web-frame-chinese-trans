<?php
/**
 * Illuminate，基础，验证，验证请求
 */

namespace Illuminate\Foundation\Validation;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Foundation\Precognition;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesRequests
{
    /**
     * Run the validation routine against the given validator.
	 * 运行给定routine针对给定的验证程序
     *
     * @param  \Illuminate\Contracts\Validation\Validator|array  $validator
     * @param  \Illuminate\Http\Request|null  $request
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateWith($validator, Request $request = null)
    {
        $request = $request ?: request();

        if (is_array($validator)) {
            $validator = $this->getValidationFactory()->make($request->all(), $validator);
        }

        if ($request->isPrecognitive()) {
            $validator->after(Precognition::afterValidationHook($request))
                ->setRules(
                    $request->filterPrecognitiveRules($validator->getRulesWithoutPlaceholders())
                );
        }

        return $validator->validate();
    }

    /**
     * Validate the given request with the given rules.
	 * 用给定的规则验证给定的请求。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(Request $request, array $rules,
                             array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make(
            $request->all(), $rules, $messages, $customAttributes
        );

        if ($request->isPrecognitive()) {
            $validator->after(Precognition::afterValidationHook($request))
                ->setRules(
                    $request->filterPrecognitiveRules($validator->getRulesWithoutPlaceholders())
                );
        }

        return $validator->validate();
    }

    /**
     * Validate the given request with the given rules.
	 * 用给定的规则验证给定的请求
     *
     * @param  string  $errorBag
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateWithBag($errorBag, Request $request, array $rules,
                                    array $messages = [], array $customAttributes = [])
    {
        try {
            return $this->validate($request, $rules, $messages, $customAttributes);
        } catch (ValidationException $e) {
            $e->errorBag = $errorBag;

            throw $e;
        }
    }

    /**
     * Get a validation factory instance.
	 * 获取验证工厂实例
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app(Factory::class);
    }
}
