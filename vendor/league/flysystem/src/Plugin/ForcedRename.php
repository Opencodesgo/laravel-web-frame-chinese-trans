<?php
/**
 * League，Flysystem，Plugin，强制重命名
 */

namespace League\Flysystem\Plugin;

use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;

class ForcedRename extends AbstractPlugin
{
    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return 'forceRename';
    }

    /**
     * Renames a file, overwriting the destination if it exists.
	 * 重新命名文件,如果存在的话,将目的地重写
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws FileNotFoundException Thrown if $path does not exist.
     * @throws FileExistsException
     *
     * @return bool True on success, false on failure.
     */
    public function handle($path, $newpath)
    {
        try {
            $deleted = $this->filesystem->delete($newpath);
        } catch (FileNotFoundException $e) {
            // The destination path does not exist. That's ok.
            $deleted = true;
        }

        if ($deleted) {
            return $this->filesystem->rename($path, $newpath);
        }

        return false;
    }
}
