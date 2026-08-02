<?php
/**
 * Illuminate，路由，路由 URI
 */

namespace Illuminate\Routing;

class RouteUri
{
    /**
     * The route URI.
	 * 路由URI
     *
     * @var string
     */
    public $uri;

    /**
     * The fields that should be used when resolving bindings.
	 * 解析绑定时应该使用的字段
     *
     * @var array
     */
    public $bindingFields = [];

    /**
     * Create a new route URI instance.
	 * 创建新的路由URI实例
     *
     * @param  string  $uri
     * @param  array  $bindingFields
     * @return void
     */
    public function __construct(string $uri, array $bindingFields = [])
    {
        $this->uri = $uri;
        $this->bindingFields = $bindingFields;
    }

    /**
     * Parse the given URI.
	 * 解析给定URI
     *
     * @param  string  $uri
     * @return static
     */
    public static function parse($uri)
    {
        preg_match_all('/\{([\w\:]+?)\??\}/', $uri, $matches);

        $bindingFields = [];

        foreach ($matches[0] as $match) {
            if (strpos($match, ':') === false) {
                continue;
            }

            $segments = explode(':', trim($match, '{}?'));

            $bindingFields[$segments[0]] = $segments[1];

            $uri = strpos($match, '?') !== false
                    ? str_replace($match, '{'.$segments[0].'?}', $uri)
                    : str_replace($match, '{'.$segments[0].'}', $uri);
        }

        return new static($uri, $bindingFields);
    }
}
