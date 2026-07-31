<?php
/**
 * Dotenv，Store，文件，路径
 */

declare(strict_types=1);

namespace Dotenv\Store\File;

/**
 * @internal
 */
final class Paths
{
    /**
     * This class is a singleton.
	 * 这个类是单例
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    private function __construct()
    {
        //
    }

    /**
     * Returns the full paths to the files.
	 * 返回文件的完整路径
     *
     * @param string[] $paths
     * @param string[] $names
     *
     * @return string[]
     */
    public static function filePaths(array $paths, array $names)
    {
        $files = [];

        foreach ($paths as $path) {
            foreach ($names as $name) {
                $files[] = \rtrim($path, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR.$name;
            }
        }

        return $files;
    }
}
