<?php
/**
 * Illuminate，视图，编译器，组件标签编译器
 */

namespace Illuminate\View\Compilers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\View\AnonymousComponent;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use ReflectionClass;

/**
 * @author Spatie bvba <info@spatie.be>
 * @author Taylor Otwell <taylor@laravel.com>
 */
class ComponentTagCompiler
{
    /**
     * The Blade compiler instance.
	 * Blade编译器实例
     *
     * @var \Illuminate\View\Compilers\BladeCompiler
     */
    protected $blade;

    /**
     * The component class aliases.
	 * 组件类别名
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * The "bind:" attributes that have been compiled for the current component.
	 * 已为当前组件编译的"bind:"属性
     *
     * @var array
     */
    protected $boundAttributes = [];

    /**
     * Create new component tag compiler.
	 * 创建新的组件包编译器
     *
     * @param  array  $aliases
     * @param  \Illuminate\View\Compilers\BladeCompiler|null
     * @return void
     */
    public function __construct(array $aliases = [], ?BladeCompiler $blade = null)
    {
        $this->aliases = $aliases;

        $this->blade = $blade ?: new BladeCompiler(new Filesystem, sys_get_temp_dir());
    }

    /**
     * Compile the component and slot tags within the given string.
	 * 编译给定字符串中的组件和插槽标签
     *
     * @param  string  $value
     * @return string
     */
    public function compile(string $value)
    {
        $value = $this->compileSlots($value);

        return $this->compileTags($value);
    }

    /**
     * Compile the tags within the given string.
	 * 编译给定字符串中的标记
     *
     * @param  string  $value
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function compileTags(string $value)
    {
        $value = $this->compileSelfClosingTags($value);
        $value = $this->compileOpeningTags($value);
        $value = $this->compileClosingTags($value);

        return $value;
    }

    /**
     * Compile the opening tags within the given string.
	 * 编译给定字符串中的开始标记
     *
     * @param  string  $value
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function compileOpeningTags(string $value)
    {
        $pattern = "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                (?<attributes>
                    (?:
                        \s+
                        [\w\-:.@]+
                        (
                            =
                            (?:
                                \\\"[^\\\"]*\\\"
                                |
                                \'[^\']*\'
                                |
                                [^\'\\\"=<>]+
                            )
                        )
                    ?)*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";

        return preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes);
        }, $value);
    }

    /**
     * Compile the self-closing tags within the given string.
	 * 编译给定字符串中的自关闭标记
     *
     * @param  string  $value
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function compileSelfClosingTags(string $value)
    {
        $pattern = "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                \s*
                (?<attributes>
                    (?:
                        \s+
                        [\w\-:.@]+
                        (
                            =
                            (?:
                                \\\"[^\\\"]*\\\"
                                |
                                \'[^\']*\'
                                |
                                [^\'\\\"=<>]+
                            )
                        )?
                    )*
                    \s*
                )
            \/>
        /x";

        return preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes)."\n@endcomponentClass ";
        }, $value);
    }

    /**
     * Compile the Blade component string for the given component and attributes.
	 * 为给定的组件和属性编译Blade组件字符串
     *
     * @param  string  $component
     * @param  array  $attributes
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function componentString(string $component, array $attributes)
    {
        $class = $this->componentClass($component);

        [$data, $attributes] = $this->partitionDataAndAttributes($class, $attributes);

        $data = $data->mapWithKeys(function ($value, $key) {
            return [Str::camel($key) => $value];
        });

        // If the component doesn't exists as a class we'll assume it's a class-less
        // component and pass the component as a view parameter to the data so it
        // can be accessed within the component and we can render out the view.
		// 如果组件不以类的形式存在，我们就假定它是无类的。
        if (! class_exists($class)) {
            $parameters = [
                'view' => "'$class'",
                'data' => '['.$this->attributesToString($data->all(), $escapeBound = false).']',
            ];

            $class = AnonymousComponent::class;
        } else {
            $parameters = $data->all();
        }

        return " @component('{$class}', '{$component}', [".$this->attributesToString($parameters, $escapeBound = false).'])
<?php $component->withAttributes(['.$this->attributesToString($attributes->all()).']); ?>';
    }

    /**
     * Get the component class for a given component alias.
	 * 获取给定组件别名的组件类
     *
     * @param  string  $component
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function componentClass(string $component)
    {
        $viewFactory = Container::getInstance()->make(Factory::class);

        if (isset($this->aliases[$component])) {
            if (class_exists($alias = $this->aliases[$component])) {
                return $alias;
            }

            if ($viewFactory->exists($alias)) {
                return $alias;
            }

            throw new InvalidArgumentException(
                "Unable to locate class or view [{$alias}] for component [{$component}]."
            );
        }

        if (class_exists($class = $this->guessClassName($component))) {
            return $class;
        }

        if ($viewFactory->exists($view = $this->guessViewName($component))) {
            return $view;
        }

        throw new InvalidArgumentException(
            "Unable to locate a class or view for component [{$component}]."
        );
    }

    /**
     * Guess the class name for the given component.
	 * 猜测给定组件的类名
     *
     * @param  string  $component
     * @return string
     */
    public function guessClassName(string $component)
    {
        $namespace = Container::getInstance()
                    ->make(Application::class)
                    ->getNamespace();

        $componentPieces = array_map(function ($componentPiece) {
            return ucfirst(Str::camel($componentPiece));
        }, explode('.', $component));

        return $namespace.'View\\Components\\'.implode('\\', $componentPieces);
    }

    /**
     * Guess the view name for the given component.
	 * 猜测给定组件的视图名称
     *
     * @param  string  $name
     * @return string
     */
    public function guessViewName($name)
    {
        $prefix = 'components.';

        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;

        if (Str::contains($name, $delimiter)) {
            return Str::replaceFirst($delimiter, $delimiter.$prefix, $name);
        }

        return $prefix.$name;
    }

    /**
     * Partition the data and extra attributes from the given array of attributes.
	 * 从给定的属性数组中划分数据和额外属性
     *
     * @param  string  $class
     * @param  array  $attributes
     * @return array
     */
    protected function partitionDataAndAttributes($class, array $attributes)
    {
        // If the class doesn't exists, we'll assume it's a class-less component and
        // return all of the attributes as both data and attributes since we have
        // now way to partition them. The user can exclude attributes manually.
		// 如果类不存在，我们就假定它是一个无类组件。
        if (! class_exists($class)) {
            return [collect($attributes), collect($attributes)];
        }

        $constructor = (new ReflectionClass($class))->getConstructor();

        $parameterNames = $constructor
                    ? collect($constructor->getParameters())->map->getName()->all()
                    : [];

        return collect($attributes)->partition(function ($value, $key) use ($parameterNames) {
            return in_array(Str::camel($key), $parameterNames);
        })->all();
    }

    /**
     * Compile the closing tags within the given string.
	 * 编译给定字符串中的结束标记
     *
     * @param  string  $value
     * @return string
     */
    protected function compileClosingTags(string $value)
    {
        return preg_replace("/<\/\s*x[-\:][\w\-\:\.]*\s*>/", ' @endcomponentClass ', $value);
    }

    /**
     * Compile the slot tags within the given string.
	 * 编译给定字符串中的槽标记
     *
     * @param  string  $value
     * @return string
     */
    public function compileSlots(string $value)
    {
        $value = preg_replace_callback('/<\s*x[\-\:]slot\s+(:?)name=(?<name>(\"[^\"]+\"|\\\'[^\\\']+\\\'|[^\s>]+))\s*>/', function ($matches) {
            $name = $this->stripQuotes($matches['name']);

            if ($matches[1] !== ':') {
                $name = "'{$name}'";
            }

            return " @slot({$name}) ";
        }, $value);

        return preg_replace('/<\/\s*x[\-\:]slot[^>]*>/', ' @endslot', $value);
    }

    /**
     * Get an array of attributes from the given attribute string.
	 * 从给定的属性字符串中获取属性数组
     *
     * @param  string  $attributeString
     * @return array
     */
    protected function getAttributesFromAttributeString(string $attributeString)
    {
        $attributeString = $this->parseBindAttributes($attributeString);

        $pattern = '/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x';

        if (! preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return collect($matches)->mapWithKeys(function ($match) {
            $attribute = $match['attribute'];
            $value = $match['value'] ?? null;

            if (is_null($value)) {
                $value = 'true';

                $attribute = Str::start($attribute, 'bind:');
            }

            $value = $this->stripQuotes($value);

            if (Str::startsWith($attribute, 'bind:')) {
                $attribute = Str::after($attribute, 'bind:');

                $this->boundAttributes[$attribute] = true;
            } else {
                $value = "'".$this->compileAttributeEchos($value)."'";
            }

            return [$attribute => $value];
        })->toArray();
    }

    /**
     * Parse the "bind" attributes in a given attribute string into their fully-qualified syntax.
	 * 将给定属性字符串中的"bind"属性解析为它们的完全限定语法
     *
     * @param  string  $attributeString
     * @return string
     */
    protected function parseBindAttributes(string $attributeString)
    {
        $pattern = "/
            (?:^|\s+)     # start of the string or whitespace between attributes
            :             # attribute needs to start with a semicolon
            ([\w\-:.@]+)  # match the actual attribute name
            =             # only match attributes that have a value
        /xm";

        return preg_replace($pattern, ' bind:$1=', $attributeString);
    }

    /**
     * Compile any Blade echo statements that are present in the attribute string.
	 * 编译属性字符串中出现的任何Blade echo语句
     *
     * These echo statements need to be converted to string concatenation statements.
	 * 这些echo语句需要转换为字符串连接语句
     *
     * @param  string  $attributeString
     * @return string
     */
    protected function compileAttributeEchos(string $attributeString)
    {
        $value = $this->blade->compileEchos($attributeString);

        $value = $this->escapeSingleQuotesOutsideOfPhpBlocks($value);

        $value = str_replace('<?php echo ', '\'.', $value);
        $value = str_replace('; ?>', '.\'', $value);

        return $value;
    }

    /**
     * Escape the single quotes in the given string that are outside of PHP blocks.
	 * 转义给定字符串中PHP块之外的单引号
     *
     * @param  string  $value
     * @return string
     */
    protected function escapeSingleQuotesOutsideOfPhpBlocks(string $value)
    {
        return collect(token_get_all($value))->map(function ($token) {
            if (! is_array($token)) {
                return $token;
            }

            return $token[0] === T_INLINE_HTML
                        ? str_replace("'", "\\'", $token[1])
                        : $token[1];
        })->implode('');
    }

    /**
     * Convert an array of attributes to a string.
	 * 将属性数组转换为字符串
     *
     * @param  array  $attributes
     * @param  bool  $escapeBound
     * @return string
     */
    protected function attributesToString(array $attributes, $escapeBound = true)
    {
        return collect($attributes)
                ->map(function (string $value, string $attribute) use ($escapeBound) {
                    return $escapeBound && isset($this->boundAttributes[$attribute]) && $value !== 'true' && ! is_numeric($value)
                                ? "'{$attribute}' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute({$value})"
                                : "'{$attribute}' => {$value}";
                })
                ->implode(',');
    }

    /**
     * Strip any quotes from the given string.
	 * 从给定字符串中去掉任何引号
     *
     * @param  string  $value
     * @return string
     */
    public function stripQuotes(string $value)
    {
        return Str::startsWith($value, ['"', '\''])
                    ? substr($value, 1, -1)
                    : $value;
    }
}
