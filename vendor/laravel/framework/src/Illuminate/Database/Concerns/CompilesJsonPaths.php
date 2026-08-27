<?php
/**
 * Illuminate，数据库，问题，编译Json路径
 */

namespace Illuminate\Database\Concerns;

use Illuminate\Support\Str;

trait CompilesJsonPaths
{
    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
	 * 将给定的JSON选择器拆分为字段和可选路径，并分别包装它们。
     *
     * @param  string  $column
     * @return array
     */
    protected function wrapJsonFieldAndPath($column)
    {
        $parts = explode('->', $column, 2);

        $field = $this->wrap($parts[0]);

        $path = count($parts) > 1 ? ', '.$this->wrapJsonPath($parts[1], '->') : '';

        return [$field, $path];
    }

    /**
     * Wrap the given JSON path.
	 * 包装给定的JSON路径
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    protected function wrapJsonPath($value, $delimiter = '->')
    {
        $value = preg_replace("/([\\\\]+)?\\'/", "''", $value);

        $jsonPath = collect(explode($delimiter, $value))
            ->map(fn ($segment) => $this->wrapJsonPathSegment($segment))
            ->join('.');

        return "'$".(str_starts_with($jsonPath, '[') ? '' : '.').$jsonPath."'";
    }

    /**
     * Wrap the given JSON path segment.
	 * 包装给定的JSON路径段
     *
     * @param  string  $segment
     * @return string
     */
    protected function wrapJsonPathSegment($segment)
    {
        if (preg_match('/(\[[^\]]+\])+$/', $segment, $parts)) {
            $key = Str::beforeLast($segment, $parts[0]);

            if (! empty($key)) {
                return '"'.$key.'"'.$parts[0];
            }

            return $parts[0];
        }

        return '"'.$segment.'"';
    }
}
