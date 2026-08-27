<?php
/**
 * Illuminate，验证，可调用的验证规则
 */

namespace Illuminate\Validation;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class InvokableValidationRule implements Rule, ValidatorAwareRule
{
    /**
     * The invokable that validates the attribute.
	 * 验证属性的可调用对象
     *
     * @var \Illuminate\Contracts\Validation\InvokableRule
     */
    protected $invokable;

    /**
     * Indicates if the validation invokable failed.
	 * 指示验证可调用项是否失败
     *
     * @var bool
     */
    protected $failed = false;

    /**
     * The validation error messages.
	 * 验证错误消息
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The current validator.
	 * 当前验证器
     *
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * The data under validation.
	 * 正在验证的数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create a new explicit Invokable validation rule.
	 * 创建一个新的显式Invokable验证规则
     *
     * @param  \Illuminate\Contracts\Validation\InvokableRule  $invokable
     * @return void
     */
    protected function __construct(InvokableRule $invokable)
    {
        $this->invokable = $invokable;
    }

    /**
     * Create a new implicit or explicit Invokable validation rule.
	 * 创建一个新的隐式或显式Invokable验证规则
     *
     * @param  \Illuminate\Contracts\Validation\InvokableRule  $invokable
     * @return \Illuminate\Contracts\Validation\Rule
     */
    public static function make($invokable)
    {
        if ($invokable->implicit ?? false) {
            return new class($invokable) extends InvokableValidationRule implements ImplicitRule {};
        }

        return new InvokableValidationRule($invokable);
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
        $this->failed = false;

        if ($this->invokable instanceof DataAwareRule) {
            $this->invokable->setData($this->validator->getData());
        }

        if ($this->invokable instanceof ValidatorAwareRule) {
            $this->invokable->setValidator($this->validator);
        }

        $this->invokable->__invoke($attribute, $value, function ($attribute, $message = null) {
            $this->failed = true;

            return $this->pendingPotentiallyTranslatedString($attribute, $message);
        });

        return ! $this->failed;
    }

    /**
     * Get the underlying invokable rule.
	 * 获取底层可调用规则
     *
     * @return \Illuminate\Contracts\Validation\InvokableRule
     */
    public function invokable()
    {
        return $this->invokable;
    }

    /**
     * Get the validation error messages.
	 * 获取验证错误消息
     *
     * @return array
     */
    public function message()
    {
        return $this->messages;
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
     * Set the current validator.
	 * 设置当前验证器
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Create a pending potentially translated string.
	 * 创建一个挂起的可能已翻译的字符串
     *
     * @param  string  $attribute
     * @param  string|null  $message
     * @return \Illuminate\Translation\PotentiallyTranslatedString
     */
    protected function pendingPotentiallyTranslatedString($attribute, $message)
    {
        $destructor = $message === null
            ? fn ($message) => $this->messages[] = $message
            : fn ($message) => $this->messages[$attribute] = $message;

        return new class($message ?? $attribute, $this->validator->getTranslator(), $destructor) extends PotentiallyTranslatedString
        {
            /**
             * The callback to call when the object destructs.
			 * 对象销毁时要调用的回调函数
             *
             * @var \Closure
             */
            protected $destructor;

            /**
             * Create a new pending potentially translated string.
			 * 创建一个新的挂起的可能已翻译的字符串
             *
             * @param  string  $message
             * @param  \Illuminate\Contracts\Translation\Translator  $translator
             * @param  \Closure  $destructor
             */
            public function __construct($message, $translator, $destructor)
            {
                parent::__construct($message, $translator);

                $this->destructor = $destructor;
            }

            /**
             * Handle the object's destruction.
			 * 处理对象的销毁
             *
             * @return void
             */
            public function __destruct()
            {
                ($this->destructor)($this->toString());
            }
        };
    }
}
