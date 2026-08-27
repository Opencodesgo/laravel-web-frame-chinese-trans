<?php
/**
 * Illuminate，数据库，问题，解析搜索路径
 */

namespace Illuminate\Database\Concerns;

trait ParsesSearchPath
{
    /**
     * Parse the Postgres "search_path" configuration value into an array.
	 * 将Postgres的"search_path"配置值解析为一个数组
     *
     * @param  string|array|null  $searchPath
     * @return array
     */
    protected function parseSearchPath($searchPath)
    {
        if (is_string($searchPath)) {
            preg_match_all('/[^\s,"\']+/', $searchPath, $matches);

            $searchPath = $matches[0];
        }

        return array_map(function ($schema) {
            return trim($schema, '\'"');
        }, $searchPath ?? []);
    }
}
