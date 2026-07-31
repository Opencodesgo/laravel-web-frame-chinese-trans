<?php
/**
 * Illuminate，验证，规则，密码
 */

namespace Illuminate\Validation\Rules;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Traits\Conditionable;
use InvalidArgumentException;

class Password implements Rule, DataAwareRule, ValidatorAwareRule
{
    use Conditionable;

    /**
     * The validator performing the validation.
	 * 执行验证的验证器
     *
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    /**
     * The data under validation.
	 * 正在验证的数据
     *
     * @var array
     */
    protected $data;

    /**
     * The minimum size of the password.
	 * 密码的最小大小
     *
     * @var int
     */
    protected $min = 8;

    /**
     * If the password requires at least one uppercase and one lowercase letter.
	 * 如果密码要求至少一个大写字母和一个小写字母
     *
     * @var bool
     */
    protected $mixedCase = false;

    /**
     * If the password requires at least one letter.
	 * 如果密码至少需要一个字母
     *
     * @var bool
     */
    protected $letters = false;

    /**
     * If the password requires at least one number.
	 * 如果密码需要至少一个数字
     *
     * @var bool
     */
    protected $numbers = false;

    /**
     * If the password requires at least one symbol.
	 * 如果密码需要至少一个符号
     *
     * @var bool
     */
    protected $symbols = false;

    /**
     * If the password should has not been compromised in data leaks.
	 * 如果密码在数据泄露中没有被泄露
     *
     * @var bool
     */
    protected $uncompromised = false;

    /**
     * The number of times a password can appear in data leaks before being consider compromised.
	 * 密码在数据泄露中出现的次数，然后才被认为被泄露。
     *
     * @var int
     */
    protected $compromisedThreshold = 0;

    /**
     * Additional validation rules that should be merged into the default rules during validation.
	 * 在验证期间应该合并到默认规则中的其他验证规则
     *
     * @var array
     */
    protected $customRules = [];

    /**
     * The failure messages, if any.
	 * 失败消息（如果有）
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The callback that will generate the "default" version of the password rule.
	 * 将生成密码规则的"默认"版本的回调
     *
     * @var string|array|callable|null
     */
    public static $defaultCallback;

    /**
     * Create a new rule instance.
	 * 创建新的规则实例
     *
     * @param  int  $min
     * @return void
     */
    public function __construct($min)
    {
        $this->min = max((int) $min, 1);
    }

    /**
     * Set the default callback to be used for determining a password's default rules.
	 * 将默认回调设置为用于确定密码的默认规则
     *
     * If no arguments are passed, the default password rule configuration will be returned.
	 * 如果没有传递参数，将返回默认密码规则配置。
     *
     * @param  static|callable|null  $callback
     * @return static|null
     */
    public static function defaults($callback = null)
    {
        if (is_null($callback)) {
            return static::default();
        }

        if (! is_callable($callback) && ! $callback instanceof static) {
            throw new InvalidArgumentException('The given callback should be callable or an instance of '.static::class);
        }

        static::$defaultCallback = $callback;
    }

    /**
     * Get the default configuration of the password rule.
	 * 获取密码规则的默认配置
     *
     * @return static
     */
    public static function default()
    {
        $password = is_callable(static::$defaultCallback)
                            ? call_user_func(static::$defaultCallback)
                            : static::$defaultCallback;

        return $password instanceof Rule ? $password : static::min(8);
    }

    /**
     * Get the default configuration of the password rule and mark the field as required.
	 * 获取密码规则的默认配置，并根据需要标记该字段。
     *
     * @return array
     */
    public static function required()
    {
        return ['required', static::default()];
    }

    /**
     * Get the default configuration of the password rule and mark the field as sometimes being required.
	 * 获取密码规则的默认配置，并将该字段标记为有时是必需的。
     *
     * @return array
     */
    public static function sometimes()
    {
        return ['sometimes', static::default()];
    }

    /**
     * Set the performing validator.
	 * 设置执行验证器
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the data under validation.
	 * 设置正在验证的数据
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets the minimum size of the password.
	 * 设置密码的最小大小
     *
     * @param  int  $size
     * @return $this
     */
    public static function min($size)
    {
        return new static($size);
    }

    /**
     * Ensures the password has not been compromised in data leaks.
	 * 确保密码在数据泄露中不被泄露
     *
     * @param  int  $threshold
     * @return $this
     */
    public function uncompromised($threshold = 0)
    {
        $this->uncompromised = true;

        $this->compromisedThreshold = $threshold;

        return $this;
    }

    /**
     * Makes the password require at least one uppercase and one lowercase letter.
	 * 使密码至少需要一个大写字母和一个小写字母
     *
     * @return $this
     */
    public function mixedCase()
    {
        $this->mixedCase = true;

        return $this;
    }

    /**
     * Makes the password require at least one letter.
	 * 使密码至少需要一个字母
     *
     * @return $this
     */
    public function letters()
    {
        $this->letters = true;

        return $this;
    }

    /**
     * Makes the password require at least one number.
	 * 使密码至少需要一个数字
     *
     * @return $this
     */
    public function numbers()
    {
        $this->numbers = true;

        return $this;
    }

    /**
     * Makes the password require at least one symbol.
	 * 使密码至少需要一个符号
     *
     * @return $this
     */
    public function symbols()
    {
        $this->symbols = true;

        return $this;
    }

    /**
     * Specify additional validation rules that should be merged with the default rules during validation.
	 * 指定在验证期间应该与默认规则合并的其他验证规则
     *
     * @param  string|array  $rules
     * @return $this
     */
    public function rules($rules)
    {
        $this->customRules = Arr::wrap($rules);

        return $this;
    }

    /**
     * Determine if the validation rule passes.
	 * 确定验证规则是否通过
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->messages = [];

        $validator = Validator::make(
            $this->data,
            [$attribute => array_merge(['string', 'min:'.$this->min], $this->customRules)],
            $this->validator->customMessages,
            $this->validator->customAttributes
        )->after(function ($validator) use ($attribute, $value) {
            if (! is_string($value)) {
                return;
            }

            $value = (string) $value;

            if ($this->mixedCase && ! preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value)) {
                $validator->errors()->add($attribute, 'The :attribute must contain at least one uppercase and one lowercase letter.');
            }

            if ($this->letters && ! preg_match('/\pL/u', $value)) {
                $validator->errors()->add($attribute, 'The :attribute must contain at least one letter.');
            }

            if ($this->symbols && ! preg_match('/\p{Z}|\p{S}|\p{P}/u', $value)) {
                $validator->errors()->add($attribute, 'The :attribute must contain at least one symbol.');
            }

            if ($this->numbers && ! preg_match('/\pN/u', $value)) {
                $validator->errors()->add($attribute, 'The :attribute must contain at least one number.');
            }
        });

        if ($validator->fails()) {
            return $this->fail($validator->messages()->all());
        }

        if ($this->uncompromised && ! Container::getInstance()->make(UncompromisedVerifier::class)->verify([
            'value' => $value,
            'threshold' => $this->compromisedThreshold,
        ])) {
            return $this->fail(
                'The given :attribute has appeared in a data leak. Please choose a different :attribute.'
            );
        }

        return true;
    }

    /**
     * Get the validation error message.
	 * 获取验证错误消息
     *
     * @return array
     */
    public function message()
    {
        return $this->messages;
    }

    /**
     * Adds the given failures, and return false.
	 * 添加给定的失败，并返回false。
     *
     * @param  array|string  $messages
     * @return bool
     */
    protected function fail($messages)
    {
        $messages = collect(Arr::wrap($messages))->map(function ($message) {
            return $this->validator->getTranslator()->get($message);
        })->all();

        $this->messages = array_merge($this->messages, $messages);

        return false;
    }
}
