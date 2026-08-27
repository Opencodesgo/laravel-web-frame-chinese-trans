<?php
/**
 * Illuminate，基础，控制台，route:list 路由列表命令
 */

namespace Illuminate\Foundation\Console;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Routing\ViewController;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionFunction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Terminal;

#[AsCommand(name: 'route:list')]
class RouteListCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * The name of the console command.
	 * 控制台命令名称
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'route:list';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'List all registered routes';

    /**
     * The router instance.
	 * 路由器实例
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The table headers for the command.
	 * 命令的表头
     *
     * @var string[]
     */
    protected $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * The terminal width resolver callback.
	 * 终端宽度解析器回调
     *
     * @var \Closure|null
     */
    protected static $terminalWidthResolver;

    /**
     * The verb colors for the command.
	 * 命令的动词颜色
     *
     * @var array
     */
    protected $verbColors = [
        'ANY' => 'red',
        'GET' => 'blue',
        'HEAD' => '#6C7280',
        'OPTIONS' => '#6C7280',
        'POST' => 'yellow',
        'PUT' => 'yellow',
        'PATCH' => 'yellow',
        'DELETE' => 'red',
    ];

    /**
     * Create a new route command instance.
	 * 创建新的路由命令实例
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
    }

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $this->router->flushMiddlewareGroups();

        if (! $this->router->getRoutes()->count()) {
            return $this->components->error("Your application doesn't have any routes.");
        }

        if (empty($routes = $this->getRoutes())) {
            return $this->components->error("Your application doesn't have any routes matching the given criteria.");
        }

        $this->displayRoutes($routes);
    }

    /**
     * Compile the routes into a displayable format.
	 * 将路由编译成可显示的格式
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = collect($this->router->getRoutes())->map(function ($route) {
            return $this->getRouteInformation($route);
        })->filter()->all();

        if (($sort = $this->option('sort')) !== null) {
            $routes = $this->sortRoutes($sort, $routes);
        } else {
            $routes = $this->sortRoutes('uri', $routes);
        }

        if ($this->option('reverse')) {
            $routes = array_reverse($routes);
        }

        return $this->pluckColumns($routes);
    }

    /**
     * Get the route information for a given route.
	 * 获取给定路线的路线信息
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return $this->filterRoute([
            'domain' => $route->domain(),
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
            'middleware' => $this->getMiddleware($route),
            'vendor' => $this->isVendorRoute($route),
        ]);
    }

    /**
     * Sort the routes by a given element.
	 * 按给定元素对路由进行排序
     *
     * @param  string  $sort
     * @param  array  $routes
     * @return array
     */
    protected function sortRoutes($sort, array $routes)
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    /**
     * Remove unnecessary columns from the routes.
	 * 从路由中删除不必要的列
     *
     * @param  array  $routes
     * @return array
     */
    protected function pluckColumns(array $routes)
    {
        return array_map(function ($route) {
            return Arr::only($route, $this->getColumns());
        }, $routes);
    }

    /**
     * Display the route information on the console.
	 * 在控制台中显示路由信息
     *
     * @param  array  $routes
     * @return void
     */
    protected function displayRoutes(array $routes)
    {
        $routes = collect($routes);

        $this->output->writeln(
            $this->option('json') ? $this->asJson($routes) : $this->forCli($routes)
        );
    }

    /**
     * Get the middleware for the route.
	 * 获取路由的中间件
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return string
     */
    protected function getMiddleware($route)
    {
        return collect($this->router->gatherRouteMiddleware($route))->map(function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        })->implode("\n");
    }

    /**
     * Determine if the route has been defined outside of the application.
	 * 确定路由是否已在应用程序外部定义
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return bool
     */
    protected function isVendorRoute(Route $route)
    {
        if ($route->action['uses'] instanceof Closure) {
            $path = (new ReflectionFunction($route->action['uses']))
                                ->getFileName();
        } elseif (is_string($route->action['uses']) &&
                  str_contains($route->action['uses'], 'SerializableClosure')) {
            return false;
        } elseif (is_string($route->action['uses'])) {
            if ($this->isFrameworkController($route)) {
                return false;
            }

            $path = (new ReflectionClass($route->getControllerClass()))
                                ->getFileName();
        } else {
            return false;
        }

        return str_starts_with($path, base_path('vendor'));
    }

    /**
     * Determine if the route uses a framework controller.
	 * 确定路由是否使用框架控制器
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return bool
     */
    protected function isFrameworkController(Route $route)
    {
        return in_array($route->getControllerClass(), [
            '\Illuminate\Routing\RedirectController',
            '\Illuminate\Routing\ViewController',
        ], true);
    }

    /**
     * Filter the route by URI and / or name.
	 * 通过URI和/或名称过滤路由
     *
     * @param  array  $route
     * @return array|null
     */
    protected function filterRoute(array $route)
    {
        if (($this->option('name') && ! Str::contains((string) $route['name'], $this->option('name'))) ||
            ($this->option('path') && ! Str::contains($route['uri'], $this->option('path'))) ||
            ($this->option('method') && ! Str::contains($route['method'], strtoupper($this->option('method')))) ||
            ($this->option('domain') && ! Str::contains((string) $route['domain'], $this->option('domain'))) ||
            ($this->option('except-vendor') && $route['vendor']) ||
            ($this->option('only-vendor') && ! $route['vendor'])) {
            return;
        }

        if ($this->option('except-path')) {
            foreach (explode(',', $this->option('except-path')) as $path) {
                if (str_contains($route['uri'], $path)) {
                    return;
                }
            }
        }

        return $route;
    }

    /**
     * Get the table headers for the visible columns.
	 * 获取可见列的表头
     *
     * @return array
     */
    protected function getHeaders()
    {
        return Arr::only($this->headers, array_keys($this->getColumns()));
    }

    /**
     * Get the column names to show (lowercase table headers).
	 * 获取要显示的列名（小写表头）
     *
     * @return array
     */
    protected function getColumns()
    {
        return array_map('strtolower', $this->headers);
    }

    /**
     * Parse the column list.
	 * 解析列列表
     *
     * @param  array  $columns
     * @return array
     */
    protected function parseColumns(array $columns)
    {
        $results = [];

        foreach ($columns as $column) {
            if (str_contains($column, ',')) {
                $results = array_merge($results, explode(',', $column));
            } else {
                $results[] = $column;
            }
        }

        return array_map('strtolower', $results);
    }

    /**
     * Convert the given routes to JSON.
	 * 将给定的路由转换为JSON
     *
     * @param  \Illuminate\Support\Collection  $routes
     * @return string
     */
    protected function asJson($routes)
    {
        return $routes
            ->map(function ($route) {
                $route['middleware'] = empty($route['middleware']) ? [] : explode("\n", $route['middleware']);

                return $route;
            })
            ->values()
            ->toJson();
    }

    /**
     * Convert the given routes to regular CLI output.
	 * 将给定的路由转换为常规的CLI输出
     *
     * @param  \Illuminate\Support\Collection  $routes
     * @return array
     */
    protected function forCli($routes)
    {
        $routes = $routes->map(
            fn ($route) => array_merge($route, [
                'action' => $this->formatActionForCli($route),
                'method' => $route['method'] == 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS' ? 'ANY' : $route['method'],
                'uri' => $route['domain'] ? ($route['domain'].'/'.ltrim($route['uri'], '/')) : $route['uri'],
            ]),
        );

        $maxMethod = mb_strlen($routes->max('method'));

        $terminalWidth = $this->getTerminalWidth();

        $routeCount = $this->determineRouteCountOutput($routes, $terminalWidth);

        return $routes->map(function ($route) use ($maxMethod, $terminalWidth) {
            [
                'action' => $action,
                'domain' => $domain,
                'method' => $method,
                'middleware' => $middleware,
                'uri' => $uri,
            ] = $route;

            $middleware = Str::of($middleware)->explode("\n")->filter()->whenNotEmpty(
                fn ($collection) => $collection->map(
                    fn ($middleware) => sprintf('         %s⇂ %s', str_repeat(' ', $maxMethod), $middleware)
                )
            )->implode("\n");

            $spaces = str_repeat(' ', max($maxMethod + 6 - mb_strlen($method), 0));

            $dots = str_repeat('.', max(
                $terminalWidth - mb_strlen($method.$spaces.$uri.$action) - 6 - ($action ? 1 : 0), 0
            ));

            $dots = empty($dots) ? $dots : " $dots";

            if ($action && ! $this->output->isVerbose() && mb_strlen($method.$spaces.$uri.$action.$dots) > ($terminalWidth - 6)) {
                $action = substr($action, 0, $terminalWidth - 7 - mb_strlen($method.$spaces.$uri.$dots)).'…';
            }

            $method = Str::of($method)->explode('|')->map(
                fn ($method) => sprintf('<fg=%s>%s</>', $this->verbColors[$method] ?? 'default', $method),
            )->implode('<fg=#6C7280>|</>');

            return [sprintf(
                '  <fg=white;options=bold>%s</> %s<fg=white>%s</><fg=#6C7280>%s %s</>',
                $method,
                $spaces,
                preg_replace('#({[^}]+})#', '<fg=yellow>$1</>', $uri),
                $dots,
                str_replace('   ', ' › ', $action ?? ''),
            ), $this->output->isVerbose() && ! empty($middleware) ? "<fg=#6C7280>$middleware</>" : null];
        })
            ->flatten()
            ->filter()
            ->prepend('')
            ->push('')->push($routeCount)->push('')
            ->toArray();
    }

    /**
     * Get the formatted action for display on the CLI.
	 * 获取格式化的操作以便在CLI上显示
     *
     * @param  array  $route
     * @return string
     */
    protected function formatActionForCli($route)
    {
        ['action' => $action, 'name' => $name] = $route;

        if ($action === 'Closure' || $action === ViewController::class) {
            return $name;
        }

        $name = $name ? "$name   " : null;

        $rootControllerNamespace = $this->laravel[UrlGenerator::class]->getRootControllerNamespace()
            ?? ($this->laravel->getNamespace().'Http\\Controllers');

        if (str_starts_with($action, $rootControllerNamespace)) {
            return $name.substr($action, mb_strlen($rootControllerNamespace) + 1);
        }

        $actionClass = explode('@', $action)[0];

        if (class_exists($actionClass) && str_starts_with((new ReflectionClass($actionClass))->getFilename(), base_path('vendor'))) {
            $actionCollection = collect(explode('\\', $action));

            return $name.$actionCollection->take(2)->implode('\\').'   '.$actionCollection->last();
        }

        return $name.$action;
    }

    /**
     * Determine and return the output for displaying the number of routes in the CLI output.
	 * 确定并返回命令行输出中显示路由数量的输出
     *
     * @param  \Illuminate\Support\Collection  $routes
     * @param  int  $terminalWidth
     * @return string
     */
    protected function determineRouteCountOutput($routes, $terminalWidth)
    {
        $routeCountText = 'Showing ['.$routes->count().'] routes';

        $offset = $terminalWidth - mb_strlen($routeCountText) - 2;

        $spaces = str_repeat(' ', $offset);

        return $spaces.'<fg=blue;options=bold>Showing ['.$routes->count().'] routes</>';
    }

    /**
     * Get the terminal width.
	 * 得到终端宽度
     *
     * @return int
     */
    public static function getTerminalWidth()
    {
        return is_null(static::$terminalWidthResolver)
            ? (new Terminal)->getWidth()
            : call_user_func(static::$terminalWidthResolver);
    }

    /**
     * Set a callback that should be used when resolving the terminal width.
	 * 设置一个在解析终端宽度时应该使用的回调
     *
     * @param  \Closure|null  $resolver
     * @return void
     */
    public static function resolveTerminalWidthUsing($resolver)
    {
        static::$terminalWidthResolver = $resolver;
    }

    /**
     * Get the console command options.
	 * 获取控制台命令选项
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['json', null, InputOption::VALUE_NONE, 'Output the route list as JSON'],
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name'],
            ['domain', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by domain'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Only show routes matching the given path pattern'],
            ['except-path', null, InputOption::VALUE_OPTIONAL, 'Do not display the routes matching the given path pattern'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes'],
            ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (domain, method, uri, name, action, middleware) to sort by', 'uri'],
            ['except-vendor', null, InputOption::VALUE_NONE, 'Do not display routes defined by vendor packages'],
            ['only-vendor', null, InputOption::VALUE_NONE, 'Only display routes defined by vendor packages'],
        ];
    }
}
