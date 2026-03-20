<?php
/**
 * League，Flysystem，Util，流式哈希
 */

namespace League\Flysystem\Util;

class StreamHasher
{
    /**
     * @var string
     */
    private $algo;

    /**
     * StreamHasher constructor.
	 * StreamHasher 构造函数
     *
     * @param string $algo
     */
    public function __construct($algo)
    {
        $this->algo = $algo;
    }

    /**
     * @param resource $resource
     *
     * @return string
     */
    public function hash($resource)
    {
        rewind($resource);
        $context = hash_init($this->algo);
        hash_update_stream($context, $resource);
        fclose($resource);

        return hash_final($context);
    }
}
