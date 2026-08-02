<?php
/**
 * Illuminate，视图，编译器，Blade编译器
 * 服务容器绑定无
 */

namespace Illuminate\View\Compilers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BladeCompiler extends Compiler implements CompilerInterface
{
    use Concerns\CompilesAuthorizations,
        Concerns\CompilesComments,
        Concerns\CompilesComponents,
        Concerns\CompilesConditionals,
        Concerns\CompilesEchos,
        Concerns\CompilesErrors,
        Concerns\CompilesHelpers,
        Concerns\CompilesIncludes,
        Concerns\CompilesInjections,
        Concerns\CompilesJson,
        Concerns\CompilesLayouts,
        Concerns\CompilesLoops,
        Concerns\CompilesRawPhp,
        Concerns\CompilesStacks,
        Concerns\CompilesTranslations;

    /**
     * All of the registered extensions.
	 * 所有已注册扩展
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * All custom "directive" handlers.
	 * 所有自定义"指令"处理程序
     *
     * @var array
     */
    protected $customDirectives = [];

    /**
     * All custom "condition" handlers.
	 * 所有自定义"条件"处理程序
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * All of the registered precompilers.
	 * 所有注册的预编译器
     *
     * @var array
     */
    protected $precompilers = [];

    /**
     * The file currently being compiled.
	 * 当前正在编译的文件
     *
     * @var string
     */
    protected $path;

    /**
     * All of the available compiler functions.
	 * 所有可用的编译器函数
     *
     * @var array
     */
    protected $compilers = [
        // 'Comments',
        'Extensions',
        'Statements',
        'Echos',
    ];

    /**
     * Array of opening and closing tags for raw echos.
	 * 原始回声的开始和结束标记数组
     *
     * @var array
     */
    protected $rawTags = ['{!!', '!!}'];

    /**
     * Array of opening and closing tags for regular echos.
	 * 常规回显的开始和结束标记数组
     *
     * @var array
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
	 * 转义回显的开始和结束标记数组
     *
     * @var array
     */
    protected $escapedTags = ['{{{', '}}}'];

    /**
     * The "regular" / legacy echo string format.
	 * "常规"/遗留回显字符串格式
     *
     * @var string
     */
    protected $echoFormat = 'e(%s)';

    /**
     * Array of footer lines to be added to template.
	 * 要添加到模板中的页脚行数组
     *
     * @var array
     */
    protected $footer = [];

    /**
     * Array to temporary store the raw blocks found in the template.
	 * 数组来临时存储在模板中找到的原始块
     *
     * @var array
     */
    protected $rawBlocks = [];

    /**
     * The array of class component aliases and their class names.
	 * 类组件别名及其类名的数组
     *
     * @var array
     */
    protected $classComponentAliases = [];

    /**
     * Indicates if component tags should be compiled.
	 * 指示是否应该编译组件标记
     *
     * @var bool
     */
    protected $compilesComponentTags = true;

    /**
     * Compile the view at the given path.
	 * 在给定路径编译视图
     *
     * @param  string|null  $path
     * @return void
     */
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }

        if (! is_null($this->cachePath)) {
            $contents = $this->compileString($this->files->get($this->getPath()));

            if (! empty($this->getPath())) {
                $contents = $this->appendFilePath($contents);
            }

            $this->files->put(
                $this->getCompiledPath($this->getPath()), $contents
            );
        }
    }

    /**
     * Append the file path to the compiled string.
	 * 将文件路径附加到编译后的字符串
     *
     * @param  string  $contents
     * @return string
     */
    protected function appendFilePath($contents)
    {
        $tokens = $this->getOpenAndClosingPhpTokens($contents);

        if ($tokens->isNotEmpty() && $tokens->last() !== T_CLOSE_TAG) {
            $contents .= ' ?>';
        }

        return $contents."<?php /**PATH {$this->getPath()} ENDPATH**/ ?>";
    }

    /**
     * Get the open and closing PHP tag tokens from the given string.
	 * 从给定字符串中获取打开和关闭PHP标记令牌
     *
     * @param  string  $contents
     * @return \Illuminate\Support\Collection
     */
    protected function getOpenAndClosingPhpTokens($contents)
    {
        return collect(token_get_all($contents))
            ->pluck(0)
            ->filter(function ($token) {
                return in_array($token, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG]);
            });
    }

    /**
     * Get the path currently being compiled.
	 * 获取当前正在编译的路径
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path currently being compiled.
	 * 设置当前正在编译的路径
     *
     * @param  string  $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Compile the given Blade template contents.
	 * 编译给定的Blade模板内容
     *
     * @param  string  $value
     * @return string
     */
    public function compileString($value)
    {
        [$this->footer, $result] = [[], ''];

        // First we will compile the Blade component tags. This is a precompile style
        // step which compiles the component Blade tags into @component directives
        // that may be used by Blade. Then we should call any other precompilers.
		// 首先，我们将编译Blade组件标签。这是一种预编译样式。
        $value = $this->compileComponentTags(
            $this->compileComments($this->storeUncompiledBlocks($value))
        );

        foreach ($this->precompilers as $precompiler) {
            $value = call_user_func($precompiler, $value);
        }

        // Here we will loop through all of the tokens returned by the Zend lexer and
        // parse each one into the corresponding valid PHP. We will then have this
        // template as the correctly rendered PHP that can be rendered natively.
		// 这里我们将循环遍历Zend词法分析器返回的所有令牌。
        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        if (! empty($this->rawBlocks)) {
            $result = $this->restoreRawContent($result);
        }

        // If there are any footer lines that need to get added to a template we will
        // add them here at the end of the template. This gets used mainly for the
        // template inheritance via the extends keyword that should be appended.
		// 如果有任何页脚行需要添加到模板，我们将添加它们到模板的末尾。
        if (count($this->footer) > 0) {
            $result = $this->addFooters($result);
        }

        return $result;
    }

    /**
     * Store the blocks that do not receive compilation.
	 * 存储不接受编译的块
     *
     * @param  string  $value
     * @return string
     */
    protected function storeUncompiledBlocks($value)
    {
        if (strpos($value, '@verbatim') !== false) {
            $value = $this->storeVerbatimBlocks($value);
        }

        if (strpos($value, '@php') !== false) {
            $value = $this->storePhpBlocks($value);
        }

        return $value;
    }

    /**
     * Store the verbatim blocks and replace them with a temporary placeholder.
	 * 存储逐字块并用临时占位符替换它们
     *
     * @param  string  $value
     * @return string
     */
    protected function storeVerbatimBlocks($value)
    {
        return preg_replace_callback('/(?<!@)@verbatim(.*?)@endverbatim/s', function ($matches) {
            return $this->storeRawBlock($matches[1]);
        }, $value);
    }

    /**
     * Store the PHP blocks and replace them with a temporary placeholder.
	 * 存储PHP块并用临时占位符替换它们
     *
     * @param  string  $value
     * @return string
     */
    protected function storePhpBlocks($value)
    {
        return preg_replace_callback('/(?<!@)@php(.*?)@endphp/s', function ($matches) {
            return $this->storeRawBlock("<?php{$matches[1]}?>");
        }, $value);
    }

    /**
     * Store a raw block and return a unique raw placeholder.
	 * 存储一个原始块并返回一个唯一的原始占位符
     *
     * @param  string  $value
     * @return string
     */
    protected function storeRawBlock($value)
    {
        return $this->getRawPlaceholder(
            array_push($this->rawBlocks, $value) - 1
        );
    }

    /**
     * Compile the component tags.
	 * 编译组件标签
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComponentTags($value)
    {
        if (! $this->compilesComponentTags) {
            return $value;
        }

        return (new ComponentTagCompiler(
            $this->classComponentAliases, $this
        ))->compile($value);
    }

    /**
     * Replace the raw placeholders with the original code stored in the raw blocks.
	 * 用存储在原始块中的原始代码替换原始占位符
     *
     * @param  string  $result
     * @return string
     */
    protected function restoreRawContent($result)
    {
        $result = preg_replace_callback('/'.$this->getRawPlaceholder('(\d+)').'/', function ($matches) {
            return $this->rawBlocks[$matches[1]];
        }, $result);

        $this->rawBlocks = [];

        return $result;
    }

    /**
     * Get a placeholder to temporary mark the position of raw blocks.
	 * 获取一个占位符来临时标记原始块的位置
     *
     * @param  int|string  $replace
     * @return string
     */
    protected function getRawPlaceholder($replace)
    {
        return str_replace('#', $replace, '@__raw_block_#__@');
    }

    /**
     * Add the stored footers onto the given content.
	 * 将存储的页脚添加到给定的内容中
     *
     * @param  string  $result
     * @return string
     */
    protected function addFooters($result)
    {
        return ltrim($result, "\n")
                ."\n".implode("\n", array_reverse($this->footer));
    }

    /**
     * Parse the tokens from the template.
	 * 解析模板中的令牌
     *
     * @param  array  $token
     * @return string
     */
    protected function parseToken($token)
    {
        [$id, $content] = $token;

        if ($id == T_INLINE_HTML) {
            foreach ($this->compilers as $type) {
                $content = $this->{"compile{$type}"}($content);
            }
        }

        return $content;
    }

    /**
     * Execute the user defined extensions.
	 * 执行用户定义的扩展
     *
     * @param  string  $value
     * @return string
     */
    protected function compileExtensions($value)
    {
        foreach ($this->extensions as $compiler) {
            $value = $compiler($value, $this);
        }

        return $value;
    }

    /**
     * Compile Blade statements that start with "@".
	 * 编译以"@"开头的Blade语句
     *
     * @param  string  $value
     * @return string
     */
    protected function compileStatements($value)
    {
        return preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function ($match) {
                return $this->compileStatement($match);
            }, $value
        );
    }

    /**
     * Compile a single Blade @ statement.
	 * 编译一条Blade @语句
     *
     * @param  array  $match
     * @return string
     */
    protected function compileStatement($match)
    {
        if (Str::contains($match[1], '@')) {
            $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1];
        } elseif (isset($this->customDirectives[$match[1]])) {
            $match[0] = $this->callCustomDirective($match[1], Arr::get($match, 3));
        } elseif (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
            $match[0] = $this->$method(Arr::get($match, 3));
        }

        return isset($match[3]) ? $match[0] : $match[0].$match[2];
    }

    /**
     * Call the given directive with the given value.
	 * 用给定的值调用给定的指令
     *
     * @param  string  $name
     * @param  string|null  $value
     * @return string
     */
    protected function callCustomDirective($name, $value)
    {
        if (Str::startsWith($value, '(') && Str::endsWith($value, ')')) {
            $value = Str::substr($value, 1, -1);
        }

        return call_user_func($this->customDirectives[$name], trim($value));
    }

    /**
     * Strip the parentheses from the given expression.
	 * 从给定表达式中去掉括号
     *
     * @param  string  $expression
     * @return string
     */
    public function stripParentheses($expression)
    {
        if (Str::startsWith($expression, '(')) {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }

    /**
     * Register a custom Blade compiler.
	 * 注册一个自定义的Blade编译器
     *
     * @param  callable  $compiler
     * @return void
     */
    public function extend(callable $compiler)
    {
        $this->extensions[] = $compiler;
    }

    /**
     * Get the extensions used by the compiler.
	 * 获取编译器使用的扩展名
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Register an "if" statement directive.
	 * 注册一个"if"语句指令
     *
     * @param  string  $name
     * @param  callable  $callback
     * @return void
     */
    public function if($name, callable $callback)
    {
        $this->conditions[$name] = $callback;

        $this->directive($name, function ($expression) use ($name) {
            return $expression !== ''
                    ? "<?php if (\Illuminate\Support\Facades\Blade::check('{$name}', {$expression})): ?>"
                    : "<?php if (\Illuminate\Support\Facades\Blade::check('{$name}')): ?>";
        });

        $this->directive('unless'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php if (! \Illuminate\Support\Facades\Blade::check('{$name}', {$expression})): ?>"
                : "<?php if (! \Illuminate\Support\Facades\Blade::check('{$name}')): ?>";
        });

        $this->directive('else'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php elseif (\Illuminate\Support\Facades\Blade::check('{$name}', {$expression})): ?>"
                : "<?php elseif (\Illuminate\Support\Facades\Blade::check('{$name}')): ?>";
        });

        $this->directive('end'.$name, function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * Check the result of a condition.
	 * 检查条件的结果
     *
     * @param  string  $name
     * @param  array  $parameters
     * @return bool
     */
    public function check($name, ...$parameters)
    {
        return call_user_func($this->conditions[$name], ...$parameters);
    }

    /**
     * Register a class-based component alias directive.
	 * 注册一个基于类的组件别名指令
     *
     * @param  string  $class
     * @param  string|null  $alias
     * @param  string  $prefix
     * @return void
     */
    public function component($class, $alias = null, $prefix = '')
    {
        if (! is_null($alias) && Str::contains($alias, '\\')) {
            [$class, $alias] = [$alias, $class];
        }

        if (is_null($alias)) {
            $alias = Str::contains($class, '\\View\\Components\\')
                            ? collect(explode('\\', Str::after($class, '\\View\\Components\\')))->map(function ($segment) {
                                return Str::kebab($segment);
                            })->implode(':')
                            : Str::kebab(class_basename($class));
        }

        if (! empty($prefix)) {
            $alias = $prefix.'-'.$alias;
        }

        $this->classComponentAliases[$alias] = $class;
    }

    /**
     * Register an array of class-based components.
	 * 注册一个基于类的组件数组
     *
     * @param  array  $components
     * @param  string  $prefix
     * @return void
     */
    public function components(array $components, $prefix = '')
    {
        foreach ($components as $key => $value) {
            if (is_numeric($key)) {
                static::component($value, null, $prefix);
            } else {
                static::component($key, $value, $prefix);
            }
        }
    }

    /**
     * Get the registered class component aliases.
	 * 获取已注册的类组件别名
     *
     * @return array
     */
    public function getClassComponentAliases()
    {
        return $this->classComponentAliases;
    }

    /**
     * Register a component alias directive.
	 * 注册一个组件别名指令
     *
     * @param  string  $path
     * @param  string|null  $alias
     * @return void
     */
    public function aliasComponent($path, $alias = null)
    {
        $alias = $alias ?: Arr::last(explode('.', $path));

        $this->directive($alias, function ($expression) use ($path) {
            return $expression
                        ? "<?php \$__env->startComponent('{$path}', {$expression}); ?>"
                        : "<?php \$__env->startComponent('{$path}'); ?>";
        });

        $this->directive('end'.$alias, function ($expression) {
            return '<?php echo $__env->renderComponent(); ?>';
        });
    }

    /**
     * Register an include alias directive.
	 * 注册一个包含别名指令
     *
     * @param  string  $path
     * @param  string|null  $alias
     * @return void
     */
    public function include($path, $alias = null)
    {
        return $this->aliasInclude($path, $alias);
    }

    /**
     * Register an include alias directive.
	 * 注册一个包含别名指令
     *
     * @param  string  $path
     * @param  string|null  $alias
     * @return void
     */
    public function aliasInclude($path, $alias = null)
    {
        $alias = $alias ?: Arr::last(explode('.', $path));

        $this->directive($alias, function ($expression) use ($path) {
            $expression = $this->stripParentheses($expression) ?: '[]';

            return "<?php echo \$__env->make('{$path}', {$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });
    }

    /**
     * Register a handler for custom directives.
	 * 为自定义指令注册一个处理程序
     *
     * @param  string  $name
     * @param  callable  $handler
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function directive($name, callable $handler)
    {
        if (! preg_match('/^\w+(?:::\w+)?$/x', $name)) {
            throw new InvalidArgumentException("The directive name [{$name}] is not valid. Directive names must only contain alphanumeric characters and underscores.");
        }

        $this->customDirectives[$name] = $handler;
    }

    /**
     * Get the list of custom directives.
	 * 获取自定义指令列表
     *
     * @return array
     */
    public function getCustomDirectives()
    {
        return $this->customDirectives;
    }

    /**
     * Register a new precompiler.
	 * 注册一个新的预编译器
     *
     * @param  callable  $precompiler
     * @return void
     */
    public function precompiler(callable $precompiler)
    {
        $this->precompilers[] = $precompiler;
    }

    /**
     * Set the echo format to be used by the compiler.
	 * 设置编译器要使用的echo格式
     *
     * @param  string  $format
     * @return void
     */
    public function setEchoFormat($format)
    {
        $this->echoFormat = $format;
    }

    /**
     * Set the "echo" format to double encode entities.
	 * 将"echo"格式设置为对实体进行双编码
     *
     * @return void
     */
    public function withDoubleEncoding()
    {
        $this->setEchoFormat('e(%s, true)');
    }

    /**
     * Set the "echo" format to not double encode entities.
	 * 设置"echo"格式,而不是双编码实体
     *
     * @return void
     */
    public function withoutDoubleEncoding()
    {
        $this->setEchoFormat('e(%s, false)');
    }

    /**
     * Indicate that component tags should not be compiled.
	 * 指明不应该编译组件标记
     *
     * @return void
     */
    public function withoutComponentTags()
    {
        $this->compilesComponentTags = false;
    }
}
