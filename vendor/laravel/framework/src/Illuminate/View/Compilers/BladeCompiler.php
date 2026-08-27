<?php
/**
 * Illuminate，视图，编译器，Blade 编译器
 */

namespace Illuminate\View\Compilers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ReflectsClosures;
use Illuminate\View\Component;
use InvalidArgumentException;

class BladeCompiler extends Compiler implements CompilerInterface
{
    use Concerns\CompilesAuthorizations,
        Concerns\CompilesClasses,
        Concerns\CompilesComments,
        Concerns\CompilesComponents,
        Concerns\CompilesConditionals,
        Concerns\CompilesEchos,
        Concerns\CompilesErrors,
        Concerns\CompilesFragments,
        Concerns\CompilesHelpers,
        Concerns\CompilesIncludes,
        Concerns\CompilesInjections,
        Concerns\CompilesJson,
        Concerns\CompilesJs,
        Concerns\CompilesLayouts,
        Concerns\CompilesLoops,
        Concerns\CompilesRawPhp,
        Concerns\CompilesStacks,
        Concerns\CompilesStyles,
        Concerns\CompilesTranslations,
        ReflectsClosures;

    /**
     * All of the registered extensions.
	 * 所有已注册的扩展
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
	 * 所有自定义“条件”处理程序
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
     * @var string[]
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
     * @var string[]
     */
    protected $rawTags = ['{!!', '!!}'];

    /**
     * Array of opening and closing tags for regular echos.
	 * 常规回显的开始和结束标记数组
     *
     * @var string[]
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
	 * 转义回显的开始和结束标记数组
     *
     * @var string[]
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
     * Array of footer lines to be added to the template.
	 * 要添加到模板中的页脚行数组
     *
     * @var array
     */
    protected $footer = [];

    /**
     * Array to temporarily store the raw blocks found in the template.
	 * 数组来临时存储在模板中找到的原始块
     *
     * @var array
     */
    protected $rawBlocks = [];

    /**
     * The array of anonymous component paths to search for components in.
	 * 要在其中搜索组件的匿名组件路径数组
     *
     * @var array
     */
    protected $anonymousComponentPaths = [];

    /**
     * The array of anonymous component namespaces to autoload from.
	 * 要从中自动加载的匿名组件名称空间数组
     *
     * @var array
     */
    protected $anonymousComponentNamespaces = [];

    /**
     * The array of class component aliases and their class names.
	 * 类组件别名及其类名的数组
     *
     * @var array
     */
    protected $classComponentAliases = [];

    /**
     * The array of class component namespaces to autoload from.
	 * 要从中自动加载的类组件名称空间数组
     *
     * @var array
     */
    protected $classComponentNamespaces = [];

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

            $this->ensureCompiledDirectoryExists(
                $compiledPath = $this->getCompiledPath($this->getPath())
            );

            $this->files->put($compiledPath, $contents);
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
            $value = $precompiler($value);
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
		// 如果有任何页脚行需要添加到模板，我们将将它们添加到模板的末尾。
        if (count($this->footer) > 0) {
            $result = $this->addFooters($result);
        }

        if (! empty($this->echoHandlers)) {
            $result = $this->addBladeCompilerVariable($result);
        }

        return str_replace(
            ['##BEGIN-COMPONENT-CLASS##', '##END-COMPONENT-CLASS##'],
            '',
            $result);
    }

    /**
     * Evaluate and render a Blade string to HTML.
	 * 求值并将Blade字符串呈现为HTML
     *
     * @param  string  $string
     * @param  array  $data
     * @param  bool  $deleteCachedView
     * @return string
     */
    public static function render($string, $data = [], $deleteCachedView = false)
    {
        $component = new class($string) extends Component
        {
            protected $template;

            public function __construct($template)
            {
                $this->template = $template;
            }

            public function render()
            {
                return $this->template;
            }
        };

        $view = Container::getInstance()
                    ->make(ViewFactory::class)
                    ->make($component->resolveView(), $data);

        return tap($view->render(), function () use ($view, $deleteCachedView) {
            if ($deleteCachedView) {
                unlink($view->getPath());
            }
        });
    }

    /**
     * Render a component instance to HTML.
	 * 将组件实例呈现为HTML
     *
     * @param  \Illuminate\View\Component  $component
     * @return string
     */
    public static function renderComponent(Component $component)
    {
        $data = $component->data();

        $view = value($component->resolveView(), $data);

        if ($view instanceof View) {
            return $view->with($data)->render();
        } elseif ($view instanceof Htmlable) {
            return $view->toHtml();
        } else {
            return Container::getInstance()
                ->make(ViewFactory::class)
                ->make($view, $data)
                ->render();
        }
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
        if (str_contains($value, '@verbatim')) {
            $value = $this->storeVerbatimBlocks($value);
        }

        if (str_contains($value, '@php')) {
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
            $this->classComponentAliases, $this->classComponentNamespaces, $this
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
     * Get a placeholder to temporarily mark the position of raw blocks.
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
	 * 编译以“@”开头的Blade语句
     *
     * @param  string  $template
     * @return string
     */
    protected function compileStatements($template)
    {
        preg_match_all('/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( [\S\s]*? ) \))?/x', $template, $matches);

        $offset = 0;

        for ($i = 0; isset($matches[0][$i]); $i++) {
            $match = [
                $matches[0][$i],
                $matches[1][$i],
                $matches[2][$i],
                $matches[3][$i] ?: null,
                $matches[4][$i] ?: null,
            ];

            // Here we check to see if we have properly found the closing parenthesis by
            // regex pattern or not, and will recursively continue on to the next ")"
            // then check again until the tokenizer confirms we find the right one.
			// 这里我们检查是否正确地找到了右括号。
            while (isset($match[4]) &&
                   Str::endsWith($match[0], ')') &&
                   ! $this->hasEvenNumberOfParentheses($match[0])) {
                if (($after = Str::after($template, $match[0])) === $template) {
                    break;
                }

                $rest = Str::before($after, ')');

                if (isset($matches[0][$i + 1]) && Str::contains($rest.')', $matches[0][$i + 1])) {
                    unset($matches[0][$i + 1]);
                    $i++;
                }

                $match[0] = $match[0].$rest.')';
                $match[3] = $match[3].$rest.')';
                $match[4] = $match[4].$rest;
            }

            [$template, $offset] = $this->replaceFirstStatement(
                $match[0],
                $this->compileStatement($match),
                $template,
                $offset
            );
        }

        return $template;
    }

    /**
     * Replace the first match for a statement compilation operation.
	 * 替换语句编译操作的第一个匹配项
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @param  int  $offset
     * @return array
     */
    protected function replaceFirstStatement($search, $replace, $subject, $offset)
    {
        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }

        $position = strpos($subject, $search, $offset);

        if ($position !== false) {
            return [
                substr_replace($subject, $replace, $position, strlen($search)),
                $position + strlen($replace),
            ];
        }

        return [$subject, 0];
    }

    /**
     * Determine if the given expression has the same number of opening and closing parentheses.
	 * 确定给定表达式是否具有相同数量的左括号和右括号
     *
     * @param  string  $expression
     * @return bool
     */
    protected function hasEvenNumberOfParentheses(string $expression)
    {
        $tokens = token_get_all('<?php '.$expression);

        if (Arr::last($tokens) !== ')') {
            return false;
        }

        $opening = 0;
        $closing = 0;

        foreach ($tokens as $token) {
            if ($token == ')') {
                $closing++;
            } elseif ($token == '(') {
                $opening++;
            }
        }

        return $opening === $closing;
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
        if (str_contains($match[1], '@')) {
            $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1];
        } elseif (isset($this->customDirectives[$match[1]])) {
            $match[0] = $this->callCustomDirective($match[1], Arr::get($match, 3));
        } elseif (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
            $match[0] = $this->$method(Arr::get($match, 3));
        } else {
            return $match[0];
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
        $value ??= '';

        if (str_starts_with($value, '(') && str_ends_with($value, ')')) {
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
	 * 注册一个“if”语句指令
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
     * @param  mixed  ...$parameters
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
        if (! is_null($alias) && str_contains($alias, '\\')) {
            [$class, $alias] = [$alias, $class];
        }

        if (is_null($alias)) {
            $alias = str_contains($class, '\\View\\Components\\')
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
                $this->component($value, null, $prefix);
            } else {
                $this->component($key, $value, $prefix);
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
     * Register a new anonymous component path.
	 * 注册一个新的匿名组件路径
     *
     * @param  string  $path
     * @param  string|null  $prefix
     * @return void
     */
    public function anonymousComponentPath(string $path, string $prefix = null)
    {
        $prefixHash = md5($prefix ?: $path);

        $this->anonymousComponentPaths[] = [
            'path' => $path,
            'prefix' => $prefix,
            'prefixHash' => $prefixHash,
        ];

        Container::getInstance()
                ->make(ViewFactory::class)
                ->addNamespace($prefixHash, $path);
    }

    /**
     * Register an anonymous component namespace.
	 * 注册一个匿名组件命名空间
     *
     * @param  string  $directory
     * @param  string|null  $prefix
     * @return void
     */
    public function anonymousComponentNamespace(string $directory, string $prefix = null)
    {
        $prefix ??= $directory;

        $this->anonymousComponentNamespaces[$prefix] = Str::of($directory)
                ->replace('/', '.')
                ->trim('. ')
                ->toString();
    }

    /**
     * Register a class-based component namespace.
	 * 注册一个基于类的组件命名空间
     *
     * @param  string  $namespace
     * @param  string  $prefix
     * @return void
     */
    public function componentNamespace($namespace, $prefix)
    {
        $this->classComponentNamespaces[$prefix] = $namespace;
    }

    /**
     * Get the registered anonymous component paths.
	 * 获取已注册的匿名组件路径
     *
     * @return array
     */
    public function getAnonymousComponentPaths()
    {
        return $this->anonymousComponentPaths;
    }

    /**
     * Get the registered anonymous component namespaces.
	 * 获取已注册的匿名组件名称空间
     *
     * @return array
     */
    public function getAnonymousComponentNamespaces()
    {
        return $this->anonymousComponentNamespaces;
    }

    /**
     * Get the registered class component namespaces.
	 * 获取已注册的类组件名称空间
     *
     * @return array
     */
    public function getClassComponentNamespaces()
    {
        return $this->classComponentNamespaces;
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
        $this->aliasInclude($path, $alias);
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
	 * 将"echo"格式设置为不对实体进行双重编码
     *
     * @return void
     */
    public function withoutDoubleEncoding()
    {
        $this->setEchoFormat('e(%s, false)');
    }

    /**
     * Indicate that component tags should not be compiled.
	 * 指示不应该编译组件标记
     *
     * @return void
     */
    public function withoutComponentTags()
    {
        $this->compilesComponentTags = false;
    }
}
