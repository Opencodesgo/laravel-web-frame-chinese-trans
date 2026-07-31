<?php
/**
 * Illuminate，路由，排序的中间件
 */

namespace Illuminate\Routing;

use Illuminate\Support\Collection;

class SortedMiddleware extends Collection
{
    /**
     * Create a new Sorted Middleware container.
	 * 创建一个新的Sorted Middleware容器
     *
     * @param  array  $priorityMap
     * @param  \Illuminate\Support\Collection|array  $middlewares
     * @return void
     */
    public function __construct(array $priorityMap, $middlewares)
    {
        if ($middlewares instanceof Collection) {
            $middlewares = $middlewares->all();
        }

        $this->items = $this->sortMiddleware($priorityMap, $middlewares);
    }

    /**
     * Sort the middlewares by the given priority map.
	 * 根据给定的优先级映射对中间件进行排序
     *
     * Each call to this method makes one discrete middleware movement if necessary.
	 * 如果有必要，对该方法的每次调用都会进行一次离散的中间件移动。
     *
     * @param  array  $priorityMap
     * @param  array  $middlewares
     * @return array
     */
    protected function sortMiddleware($priorityMap, $middlewares)
    {
        $lastIndex = 0;

        foreach ($middlewares as $index => $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            $priorityIndex = $this->priorityMapIndex($priorityMap, $middleware);

            if (! is_null($priorityIndex)) {
                // This middleware is in the priority map. If we have encountered another middleware
                // that was also in the priority map and was at a lower priority than the current
                // middleware, we will move this middleware to be above the previous encounter.
				// 此中间件位于优先级映射中。
                if (isset($lastPriorityIndex) && $priorityIndex < $lastPriorityIndex) {
                    return $this->sortMiddleware(
                        $priorityMap, array_values($this->moveMiddleware($middlewares, $index, $lastIndex))
                    );
                }

                // This middleware is in the priority map; but, this is the first middleware we have
                // encountered from the map thus far. We'll save its current index plus its index
                // from the priority map so we can compare against them on the next iterations.
				// 这个中间件位于优先级图中；
                $lastIndex = $index;

                $lastPriorityIndex = $priorityIndex;
            }
        }

        return Router::uniqueMiddleware($middlewares);
    }

    /**
     * Calculate the priority map index of the middleware.
	 * 计算中间件的优先级映射索引
     *
     * @param  array  $priorityMap
     * @param  string  $middleware
     * @return int|null
     */
    protected function priorityMapIndex($priorityMap, $middleware)
    {
        foreach ($this->middlewareNames($middleware) as $name) {
            $priorityIndex = array_search($name, $priorityMap);

            if ($priorityIndex !== false) {
                return $priorityIndex;
            }
        }
    }

    /**
     * Resolve the middleware names to look for in the priority array.
	 * 解析要在优先级数组中查找的中间件名称
     *
     * @param  string  $middleware
     * @return \Generator
     */
    protected function middlewareNames($middleware)
    {
        $stripped = head(explode(':', $middleware));

        yield $stripped;

        $interfaces = @class_implements($stripped);

        if ($interfaces !== false) {
            foreach ($interfaces as $interface) {
                yield $interface;
            }
        }
    }

    /**
     * Splice a middleware into a new position and remove the old entry.
	 * 将中间件拼接到新位置并删除旧条目
     *
     * @param  array  $middlewares
     * @param  int  $from
     * @param  int  $to
     * @return array
     */
    protected function moveMiddleware($middlewares, $from, $to)
    {
        array_splice($middlewares, $to, 0, $middlewares[$from]);

        unset($middlewares[$from + 1]);

        return $middlewares;
    }
}
