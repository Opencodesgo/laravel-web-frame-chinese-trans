<?php
/**
 * League，Flysystem，文件
 */

namespace League\Flysystem;

/**
 * @deprecated
 */
class File extends Handler
{
    /**
     * Check whether the file exists.
	 * 检查文件是否存在
     *
     * @return bool
     */
    public function exists()
    {
        return $this->filesystem->has($this->path);
    }

    /**
     * Read the file.
	 * 读取文件
     *
     * @return string|false file contents
     */
    public function read()
    {
        return $this->filesystem->read($this->path);
    }

    /**
     * Read the file as a stream.
	 * 将文件读取为流
     *
     * @return resource|false file stream
     */
    public function readStream()
    {
        return $this->filesystem->readStream($this->path);
    }

    /**
     * Write the new file.
	 * 编写新文件
     *
     * @param string $content
     *
     * @return bool success boolean
     */
    public function write($content)
    {
        return $this->filesystem->write($this->path, $content);
    }

    /**
     * Write the new file using a stream.
	 * 使用流编写新文件
     *
     * @param resource $resource
     *
     * @return bool success boolean
     */
    public function writeStream($resource)
    {
        return $this->filesystem->writeStream($this->path, $resource);
    }

    /**
     * Update the file contents.
	 * 更新文件内容
     *
     * @param string $content
     *
     * @return bool success boolean
     */
    public function update($content)
    {
        return $this->filesystem->update($this->path, $content);
    }

    /**
     * Update the file contents with a stream.
	 * 用流更新文件内容
     *
     * @param resource $resource
     *
     * @return bool success boolean
     */
    public function updateStream($resource)
    {
        return $this->filesystem->updateStream($this->path, $resource);
    }

    /**
     * Create the file or update if exists.
	 * 如果存在,创建文件或更新
     *
     * @param string $content
     *
     * @return bool success boolean
     */
    public function put($content)
    {
        return $this->filesystem->put($this->path, $content);
    }

    /**
     * Create the file or update if exists using a stream.
	 * 如果存在使用流,创建文件或更新
     *
     * @param resource $resource
     *
     * @return bool success boolean
     */
    public function putStream($resource)
    {
        return $this->filesystem->putStream($this->path, $resource);
    }

    /**
     * Rename the file.
	 * 重命名文件
     *
     * @param string $newpath
     *
     * @return bool success boolean
     */
    public function rename($newpath)
    {
        if ($this->filesystem->rename($this->path, $newpath)) {
            $this->path = $newpath;

            return true;
        }

        return false;
    }

    /**
     * Copy the file.
	 * 复制文件
     *
     * @param string $newpath
     *
     * @return File|false new file or false
     */
    public function copy($newpath)
    {
        if ($this->filesystem->copy($this->path, $newpath)) {
            return new File($this->filesystem, $newpath);
        }

        return false;
    }

    /**
     * Get the file's timestamp.
	 * 获取文件的时间戳
     *
     * @return string|false The timestamp or false on failure.
     */
    public function getTimestamp()
    {
        return $this->filesystem->getTimestamp($this->path);
    }

    /**
     * Get the file's mimetype.
	 * 获取文件的mimetype
     *
     * @return string|false The file mime-type or false on failure.
     */
    public function getMimetype()
    {
        return $this->filesystem->getMimetype($this->path);
    }

    /**
     * Get the file's visibility.
	 * 获取文件的可见性
     *
     * @return string|false The visibility (public|private) or false on failure.
     */
    public function getVisibility()
    {
        return $this->filesystem->getVisibility($this->path);
    }

    /**
     * Get the file's metadata.
	 * 获取文件的元数据
     *
     * @return array|false The file metadata or false on failure.
     */
    public function getMetadata()
    {
        return $this->filesystem->getMetadata($this->path);
    }

    /**
     * Get the file size.
	 * 获取文件大小
     *
     * @return int|false The file size or false on failure.
     */
    public function getSize()
    {
        return $this->filesystem->getSize($this->path);
    }

    /**
     * Delete the file.
	 * 删除文件
     *
     * @return bool success boolean
     */
    public function delete()
    {
        return $this->filesystem->delete($this->path);
    }
}
