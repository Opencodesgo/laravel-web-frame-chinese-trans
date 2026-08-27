<?php
/**
 * Illuminate，验证，工厂
 */

namespace Illuminate\Validation;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Factory as FactoryContract;
use Illuminate\Support\Str;

class Factory implements FactoryContract
{
    /**
     * The Translator implementation.
	 * Translator实现
     *
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * The Presence Verifier implementation.
	 * 状态验证器实现
     *
     * @var \Illuminate\Validation\PresenceVerifierInterface
     */
    protected $verifier;

    /**
     * The IoC container instance.
	 * IoC容器实例
     *
     * @var \Illuminate\Contracts\Container\Container|null
     */
    protected $container;

    /**
     * All of the custom validator extensions.
	 * 所有自定义验证器扩展
     *
     * @var array<string, \Closure|string>
     */
    protected $extensions = [];

    /**
     * All of the custom implicit validator extensions.
	 * 所有自定义隐式验证器扩展
     *
     * @var array<string, \Closure|string>
     */
    protected $implicitExtensions = [];

    /**
     * All of the custom dependent validator extensions.
	 * 所有自定义依赖验证器扩展
     *
     * @var array<string, \Closure|string>
     */
    protected $dependentExtensions = [];

    /**
     * All of the custom validator message replacers.
	 * 所有自定义验证器消息替换程序
     *
     * @var array<string, \Closure|string>
     */
    protected $replacers = [];

    /**
     * All of the fallback messages for custom rules.
	 * 自定义规则的所有回退消息
     *
     * @var array<string, string>
     */
    protected $fallbackMessages = [];

    /**
     * Indicates that unvalidated array keys should be excluded, even if the parent array was validated.
	 * 指示即使验证了父数组，也应排除未验证的数组键。
     *
     * @var bool
     */
    protected $excludeUnvalidatedArrayKeys = true;

    /**
     * The Validator resolver instance.
	 * 验证器解析器实例
     *
     * @var \Closure
     */
    protected $resolver;

    /**
     * Create a new Validator factory instance.
	 * 创建一个新的Validator工厂实例
     *
     * @param  \Illuminate\Contracts\Translation\Translator  $translator
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     * @return void
     */
    public function __construct(Translator $translator, Container $container = null)
    {
        $this->container = $container;
        $this->translator = $translator;
    }

    /**
     * Create a new Validator instance.
	 * 创建一个新的Validator实例
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return \Illuminate\Validation\Validator
     */
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->resolve(
            $data, $rules, $messages, $customAttributes
        );

        // The presence verifier is responsible for checking the unique and exists data
        // for the validator. It is behind an interface so that multiple versions of
        // it may be written besides database. We'll inject it into the validator.
		// 状态验证器负责检查唯一的和验证器存在的数据。
        if (! is_null($this->verifier)) {
            $validator->setPresenceVerifier($this->verifier);
        }

        // Next we'll set the IoC container instance of the validator, which is used to
        // resolve out class based validator extensions. If it is not set then these
        // types of extensions will not be possible on these validation instances.
		// 接下来，我们将设置验证器的IoC容器实例，我将被用于解析出基于类的验证器扩展。
        if (! is_null($this->container)) {
            $validator->setContainer($this->container);
        }

        $validator->excludeUnvalidatedArrayKeys = $this->excludeUnvalidatedArrayKeys;

        $this->addExtensions($validator);

        return $validator;
    }

    /**
     * Validate the given data against the provided rules.
	 * 根据提供的规则验证给定的数据
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        return $this->make($data, $rules, $messages, $customAttributes)->validate();
    }

    /**
     * Resolve a new Validator instance.
	 * 解析一个新的Validator实例
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return \Illuminate\Validation\Validator
     */
    protected function resolve(array $data, array $rules, array $messages, array $customAttributes)
    {
        if (is_null($this->resolver)) {
            return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
        }

        return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
    }

    /**
     * Add the extensions to a validator instance.
	 * 将扩展添加到验证器实例
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    protected function addExtensions(Validator $validator)
    {
        $validator->addExtensions($this->extensions);

        // Next, we will add the implicit extensions, which are similar to the required
        // and accepted rule in that they're run even if the attributes aren't in an
        // array of data which is given to a validator instance via instantiation.
		// 接下来，我们将添加隐式扩展，这与必需的扩展类似。
        $validator->addImplicitExtensions($this->implicitExtensions);

        $validator->addDependentExtensions($this->dependentExtensions);

        $validator->addReplacers($this->replacers);

        $validator->setFallbackMessages($this->fallbackMessages);
    }

    /**
     * Register a custom validator extension.
	 * 注册一个自定义验证器扩展
     *
     * @param  string  $rule
     * @param  \Closure|string  $extension
     * @param  string|null  $message
     * @return void
     */
    public function extend($rule, $extension, $message = null)
    {
        $this->extensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[Str::snake($rule)] = $message;
        }
    }

    /**
     * Register a custom implicit validator extension.
	 * 注册自定义隐式验证器扩展
     *
     * @param  string  $rule
     * @param  \Closure|string  $extension
     * @param  string|null  $message
     * @return void
     */
    public function extendImplicit($rule, $extension, $message = null)
    {
        $this->implicitExtensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[Str::snake($rule)] = $message;
        }
    }

    /**
     * Register a custom dependent validator extension.
	 * 注册自定义依赖验证器扩展
     *
     * @param  string  $rule
     * @param  \Closure|string  $extension
     * @param  string|null  $message
     * @return void
     */
    public function extendDependent($rule, $extension, $message = null)
    {
        $this->dependentExtensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[Str::snake($rule)] = $message;
        }
    }

    /**
     * Register a custom validator message replacer.
	 * 注册一个自定义验证器消息替换程序
     *
     * @param  string  $rule
     * @param  \Closure|string  $replacer
     * @return void
     */
    public function replacer($rule, $replacer)
    {
        $this->replacers[$rule] = $replacer;
    }

    /**
     * Indicate that unvalidated array keys should be included in validated data when the parent array is validated.
	 * 指示在验证父数组时，应将未验证的数组键包含在已验证的数据中。
     *
     * @return void
     */
    public function includeUnvalidatedArrayKeys()
    {
        $this->excludeUnvalidatedArrayKeys = false;
    }

    /**
     * Indicate that unvalidated array keys should be excluded from the validated data, even if the parent array was validated.
	 * 指示应将未经验证的数组键从已验证的数据中排除，即使父数组已经过验证。
     *
     * @return void
     */
    public function excludeUnvalidatedArrayKeys()
    {
        $this->excludeUnvalidatedArrayKeys = true;
    }

    /**
     * Set the Validator instance resolver.
	 * 设置Validator实例解析器
     *
     * @param  \Closure  $resolver
     * @return void
     */
    public function resolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Get the Translator implementation.
	 * 获取Translator实现
     *
     * @return \Illuminate\Contracts\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Get the Presence Verifier implementation.
	 * 获取Presence Verifier实现
     *
     * @return \Illuminate\Validation\PresenceVerifierInterface
     */
    public function getPresenceVerifier()
    {
        return $this->verifier;
    }

    /**
     * Set the Presence Verifier implementation.
	 * 设置状态验证器实现
     *
     * @param  \Illuminate\Validation\PresenceVerifierInterface  $presenceVerifier
     * @return void
     */
    public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
    {
        $this->verifier = $presenceVerifier;
    }

    /**
     * Get the container instance used by the validation factory.
	 * 获取验证工厂使用的容器实例
     *
     * @return \Illuminate\Contracts\Container\Container|null
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the container instance used by the validation factory.
	 * 设置验证工厂使用的容器实例
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}
