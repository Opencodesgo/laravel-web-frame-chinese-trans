<?php
/**
 * Dotenv，存储，文件，路径
 */

namespace Dotenv\Store\File;

class Paths
{
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
                $files[] = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
            }
        }

        return $files;
    }
}
