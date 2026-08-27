<?php
/**
 * Illuminate，分页，分页状态
 */

namespace Illuminate\Pagination;

class PaginationState
{
    /**
     * Bind the pagination state resolvers using the given application container as a base.
	 * 使用给定的应用程序容器作为基来绑定分页状态解析器
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public static function resolveUsing($app)
    {
        Paginator::viewFactoryResolver(fn () => $app['view']);

        Paginator::currentPathResolver(fn () => $app['request']->url());

        Paginator::currentPageResolver(function ($pageName = 'page') use ($app) {
            $page = $app['request']->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });

        Paginator::queryStringResolver(fn () => $app['request']->query());

        CursorPaginator::currentCursorResolver(function ($cursorName = 'cursor') use ($app) {
            return Cursor::fromEncoded($app['request']->input($cursorName));
        });
    }
}
