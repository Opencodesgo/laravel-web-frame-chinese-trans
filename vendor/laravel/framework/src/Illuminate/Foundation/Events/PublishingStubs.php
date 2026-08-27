<?php
/**
 * Illuminate，基础，事件，发布存根
 */

namespace Illuminate\Foundation\Events;

class PublishingStubs
{
    use Dispatchable;

    /**
     * The stubs being published.
	 * 发布存根
     *
     * @var array
     */
    public $stubs = [];

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  array  $stubs
     * @return void
     */
    public function __construct(array $stubs)
    {
        $this->stubs = $stubs;
    }

    /**
     * Add a new stub to be published.
	 * 添加要发布的新存根
     *
     * @param  string  $path
     * @param  string  $name
     * @return $this
     */
    public function add(string $path, string $name)
    {
        $this->stubs[$path] = $name;

        return $this;
    }
}
