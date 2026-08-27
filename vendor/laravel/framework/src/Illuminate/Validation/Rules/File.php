<?php
/**
 * Illuminate，验证，规则，文件
 */

namespace Illuminate\Validation\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class File implements Rule, DataAwareRule, ValidatorAwareRule
{
    use Conditionable;
    use Macroable;

    /**
     * The MIME types that the given file should match. This array may also contain file extensions.
	 * 给定文件应该匹配的MIME类型。该数组还可以包含文件扩展名。
     *
     * @var array
     */
    protected $allowedMimetypes = [];

    /**
     * The minimum size in kilobytes that the file can be.
	 * 文件的最小大小，以千字节为单位。
     *
     * @var null|int
     */
    protected $minimumFileSize = null;

    /**
     * The maximum size in kilobytes that the file can be.
	 * 文件的最大大小，以千字节为单位。
     *
     * @var null|int
     */
    protected $maximumFileSize = null;

    /**
     * An array of custom rules that will be merged into the validation rules.
	 * 将合并到验证规则中的自定义规则数组
     *
     * @var array
     */
    protected $customRules = [];

    /**
     * The error message after validation, if any.
	 * 验证后的错误消息（如果有的话）。
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The data under validation.
	 * 正在验证的数据
     *
     * @var array
     */
    protected $data;

    /**
     * The validator performing the validation.
	 * 执行验证的验证器
     *
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * The callback that will generate the "default" version of the file rule.
	 * 将生成文件规则的"默认"版本的回调
     *
     * @var string|array|callable|null
     */
    public static $defaultCallback;

    /**
     * Set the default callback to be used for determining the file default rules.
	 * 设置用于确定文件默认规则的默认回调
     *
     * If no arguments are passed, the default file rule configuration will be returned.
	 * 如果没有传递参数，将返回默认的文件规则配置。
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
     * Get the default configuration of the file rule.
	 * 获取文件规则的默认配置
     *
     * @return static
     */
    public static function default()
    {
        $file = is_callable(static::$defaultCallback)
            ? call_user_func(static::$defaultCallback)
            : static::$defaultCallback;

        return $file instanceof Rule ? $file : new self();
    }

    /**
     * Limit the uploaded file to only image types.
	 * 将上传的文件限制为仅图像类型
     *
     * @return ImageFile
     */
    public static function image()
    {
        return new ImageFile();
    }

    /**
     * Limit the uploaded file to the given MIME types or file extensions.
	 * 将上传的文件限制为给定的MIME类型或文件扩展名
     *
     * @param  string|array<int, string>  $mimetypes
     * @return static
     */
    public static function types($mimetypes)
    {
        return tap(new static(), fn ($file) => $file->allowedMimetypes = (array) $mimetypes);
    }

    /**
     * Indicate that the uploaded file should be exactly a certain size in kilobytes.
	 * 指示上传的文件应该精确到一定的大小（以千字节为单位）
     *
     * @param  int  $kilobytes
     * @return $this
     */
    public function size($kilobytes)
    {
        $this->minimumFileSize = $kilobytes;
        $this->maximumFileSize = $kilobytes;

        return $this;
    }

    /**
     * Indicate that the uploaded file should be between a minimum and maximum size in kilobytes.
	 * 指示上传的文件的大小应介于最小和最大之间，以千字节为单位。
     *
     * @param  int  $minKilobytes
     * @param  int  $maxKilobytes
     * @return $this
     */
    public function between($minKilobytes, $maxKilobytes)
    {
        $this->minimumFileSize = $minKilobytes;
        $this->maximumFileSize = $maxKilobytes;

        return $this;
    }

    /**
     * Indicate that the uploaded file should be no less than the given number of kilobytes.
	 * 指示上传的文件不应少于给定的千字节数
     *
     * @param  int  $kilobytes
     * @return $this
     */
    public function min($kilobytes)
    {
        $this->minimumFileSize = $kilobytes;

        return $this;
    }

    /**
     * Indicate that the uploaded file should be no more than the given number of kilobytes.
	 * 指示上传的文件不应超过给定的千字节数
     *
     * @param  int  $kilobytes
     * @return $this
     */
    public function max($kilobytes)
    {
        $this->maximumFileSize = $kilobytes;

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
        $this->customRules = array_merge($this->customRules, Arr::wrap($rules));

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
            [$attribute => $this->buildValidationRules()],
            $this->validator->customMessages,
            $this->validator->customAttributes
        );

        if ($validator->fails()) {
            return $this->fail($validator->messages()->all());
        }

        return true;
    }

    /**
     * Build the array of underlying validation rules based on the current state.
	 * 基于当前状态构建底层验证规则数组
     *
     * @return array
     */
    protected function buildValidationRules()
    {
        $rules = ['file'];

        $rules = array_merge($rules, $this->buildMimetypes());

        $rules[] = match (true) {
            is_null($this->minimumFileSize) && is_null($this->maximumFileSize) => null,
            is_null($this->maximumFileSize) => "min:{$this->minimumFileSize}",
            is_null($this->minimumFileSize) => "max:{$this->maximumFileSize}",
            $this->minimumFileSize !== $this->maximumFileSize => "between:{$this->minimumFileSize},{$this->maximumFileSize}",
            default => "size:{$this->minimumFileSize}",
        };

        return array_merge(array_filter($rules), $this->customRules);
    }

    /**
     * Separate the given mimetypes from extensions and return an array of correct rules to validate against.
	 * 将给定的mime类型与扩展分开，并返回要验证的正确规则数组。
     *
     * @return array
     */
    protected function buildMimetypes()
    {
        if (count($this->allowedMimetypes) === 0) {
            return [];
        }

        $rules = [];

        $mimetypes = array_filter(
            $this->allowedMimetypes,
            fn ($type) => str_contains($type, '/')
        );

        $mimes = array_diff($this->allowedMimetypes, $mimetypes);

        if (count($mimetypes) > 0) {
            $rules[] = 'mimetypes:'.implode(',', $mimetypes);
        }

        if (count($mimes) > 0) {
            $rules[] = 'mimes:'.implode(',', $mimes);
        }

        return $rules;
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
     * Set the current validator.
	 * 设置当前验证器
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
     * Set the current data under validation.
	 * 设置正在验证的当前数据
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
