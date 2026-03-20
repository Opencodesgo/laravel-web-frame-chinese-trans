<?php
/**
 * League，Flysystem，Plugin，与列表
 */

namespace League\Flysystem\Plugin;

class ListWith extends AbstractPlugin
{
    /**
     * Get the method name.
	 * 获取方法名
     *
     * @return string
     */
    public function getMethod()
    {
        return 'listWith';
    }

    /**
     * List contents with metadata.
	 * 用元数据列出内容
     *
     * @param string[] $keys
     * @param string   $directory
     * @param bool     $recursive
     *
     * @return array listing with metadata
     */
    public function handle(array $keys = [], $directory = '', $recursive = false)
    {
        $contents = $this->filesystem->listContents($directory, $recursive);

        foreach ($contents as $index => $object) {
            if ($object['type'] === 'file') {
                $missingKeys = array_diff($keys, array_keys($object));
                $contents[$index] = array_reduce($missingKeys, [$this, 'getMetadataByName'], $object);
            }
        }

        return $contents;
    }

    /**
     * Get a meta-data value by key name.
	 * 按键名获取元数据值
     *
     * @param array  $object
     * @param string $key
     *
     * @return array
     */
    protected function getMetadataByName(array $object, $key)
    {
        $method = 'get' . ucfirst($key);

        if ( ! method_exists($this->filesystem, $method)) {
            throw new \InvalidArgumentException('Could not get meta-data for key: ' . $key);
        }

        $object[$key] = $this->filesystem->{$method}($object['path']);

        return $object;
    }
}
