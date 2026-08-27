<?php
/**
 * Illuminate，基础，轻快地
 */

namespace Illuminate\Foundation;

use Exception;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class Vite implements Htmlable
{
    use Macroable;

    /**
     * The Content Security Policy nonce to apply to all generated tags.
	 * 内容安全策略将应用于所有生成的标记
     *
     * @var string|null
     */
    protected $nonce;

    /**
     * The key to check for integrity hashes within the manifest.
	 * 检查清单中完整性哈希值的键
     *
     * @var string|false
     */
    protected $integrityKey = 'integrity';

    /**
     * The configured entry points.
	 * 配置的入口点
     *
     * @var array
     */
    protected $entryPoints = [];

    /**
     * The path to the "hot" file.
	 * "hot"文件的路径
     *
     * @var string|null
     */
    protected $hotFile;

    /**
     * The path to the build directory.
	 * 构建目录的路径
     *
     * @var string
     */
    protected $buildDirectory = 'build';

    /**
     * The name of the manifest file.
	 * 清单文件的名称
     *
     * @var string
     */
    protected $manifestFilename = 'manifest.json';

    /**
     * The script tag attributes resolvers.
	 * 脚本标记属性解析器
     *
     * @var array
     */
    protected $scriptTagAttributesResolvers = [];

    /**
     * The style tag attributes resolvers.
	 * 样式标记属性解析
     *
     * @var array
     */
    protected $styleTagAttributesResolvers = [];

    /**
     * The preload tag attributes resolvers.
	 * 预加载标签属性解析器
     *
     * @var array
     */
    protected $preloadTagAttributesResolvers = [];

    /**
     * The preloaded assets.
	 * 预加载的资源
     *
     * @var array
     */
    protected $preloadedAssets = [];

    /**
     * The cached manifest files.
	 * 缓存的清单文件
     *
     * @var array
     */
    protected static $manifests = [];

    /**
     * Get the preloaded assets.
	 * 获取预加载的资源
     *
     * @return array
     */
    public function preloadedAssets()
    {
        return $this->preloadedAssets;
    }

    /**
     * Get the Content Security Policy nonce applied to all generated tags.
	 * 获取立即应用于所有生成标记的内容安全策略
     *
     * @return string|null
     */
    public function cspNonce()
    {
        return $this->nonce;
    }

    /**
     * Generate or set a Content Security Policy nonce to apply to all generated tags.
	 * 立即生成或设置内容安全策略以应用于所有生成的标记
     *
     * @param  string|null  $nonce
     * @return string
     */
    public function useCspNonce($nonce = null)
    {
        return $this->nonce = $nonce ?? Str::random(40);
    }

    /**
     * Use the given key to detect integrity hashes in the manifest.
	 * 使用给定的键来检测清单中的完整性散列
     *
     * @param  string|false  $key
     * @return $this
     */
    public function useIntegrityKey($key)
    {
        $this->integrityKey = $key;

        return $this;
    }

    /**
     * Set the Vite entry points.
	 * 设置Vite入口点
     *
     * @param  array  $entryPoints
     * @return $this
     */
    public function withEntryPoints($entryPoints)
    {
        $this->entryPoints = $entryPoints;

        return $this;
    }

    /**
     * Set the filename for the manifest file.
	 * 设置清单文件的文件名
     *
     * @param  string  $filename
     * @return $this
     */
    public function useManifestFilename($filename)
    {
        $this->manifestFilename = $filename;

        return $this;
    }

    /**
     * Get the Vite "hot" file path.
	 * 获取Vite"hot"文件路径
     *
     * @return string
     */
    public function hotFile()
    {
        return $this->hotFile ?? public_path('/hot');
    }

    /**
     * Set the Vite "hot" file path.
	 * 设置Vite"hot"文件路径
     *
     * @param  string  $path
     * @return $this
     */
    public function useHotFile($path)
    {
        $this->hotFile = $path;

        return $this;
    }

    /**
     * Set the Vite build directory.
	 * 设置Vite构建目录
     *
     * @param  string  $path
     * @return $this
     */
    public function useBuildDirectory($path)
    {
        $this->buildDirectory = $path;

        return $this;
    }

    /**
     * Use the given callback to resolve attributes for script tags.
	 * 使用给定的回调来解析脚本标记的属性
     *
     * @param  (callable(string, string, ?array, ?array): array)|array  $attributes
     * @return $this
     */
    public function useScriptTagAttributes($attributes)
    {
        if (! is_callable($attributes)) {
            $attributes = fn () => $attributes;
        }

        $this->scriptTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * Use the given callback to resolve attributes for style tags.
	 * 使用给定的回调来解析样式标记的属性
     *
     * @param  (callable(string, string, ?array, ?array): array)|array  $attributes
     * @return $this
     */
    public function useStyleTagAttributes($attributes)
    {
        if (! is_callable($attributes)) {
            $attributes = fn () => $attributes;
        }

        $this->styleTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * Use the given callback to resolve attributes for preload tags.
	 * 使用给定的回调来解析预加载标签的属性
     *
     * @param  (callable(string, string, ?array, ?array): (array|false))|array|false  $attributes
     * @return $this
     */
    public function usePreloadTagAttributes($attributes)
    {
        if (! is_callable($attributes)) {
            $attributes = fn () => $attributes;
        }

        $this->preloadTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * Generate Vite tags for an entrypoint.
	 * 为入口点生成Vite标签
     *
     * @param  string|string[]  $entrypoints
     * @param  string|null  $buildDirectory
     * @return \Illuminate\Support\HtmlString
     *
     * @throws \Exception
     */
    public function __invoke($entrypoints, $buildDirectory = null)
    {
        $entrypoints = collect($entrypoints);
        $buildDirectory ??= $this->buildDirectory;

        if ($this->isRunningHot()) {
            return new HtmlString(
                $entrypoints
                    ->prepend('@vite/client')
                    ->map(fn ($entrypoint) => $this->makeTagForChunk($entrypoint, $this->hotAsset($entrypoint), null, null))
                    ->join('')
            );
        }

        $manifest = $this->manifest($buildDirectory);

        $tags = collect();
        $preloads = collect();

        foreach ($entrypoints as $entrypoint) {
            $chunk = $this->chunk($manifest, $entrypoint);

            $preloads->push([
                $chunk['src'],
                $this->assetPath("{$buildDirectory}/{$chunk['file']}"),
                $chunk,
                $manifest,
            ]);

            foreach ($chunk['imports'] ?? [] as $import) {
                $preloads->push([
                    $import,
                    $this->assetPath("{$buildDirectory}/{$manifest[$import]['file']}"),
                    $manifest[$import],
                    $manifest,
                ]);

                foreach ($manifest[$import]['css'] ?? [] as $css) {
                    $partialManifest = Collection::make($manifest)->where('file', $css);

                    $preloads->push([
                        $partialManifest->keys()->first(),
                        $this->assetPath("{$buildDirectory}/{$css}"),
                        $partialManifest->first(),
                        $manifest,
                    ]);

                    $tags->push($this->makeTagForChunk(
                        $partialManifest->keys()->first(),
                        $this->assetPath("{$buildDirectory}/{$css}"),
                        $partialManifest->first(),
                        $manifest
                    ));
                }
            }

            $tags->push($this->makeTagForChunk(
                $entrypoint,
                $this->assetPath("{$buildDirectory}/{$chunk['file']}"),
                $chunk,
                $manifest
            ));

            foreach ($chunk['css'] ?? [] as $css) {
                $partialManifest = Collection::make($manifest)->where('file', $css);

                $preloads->push([
                    $partialManifest->keys()->first(),
                    $this->assetPath("{$buildDirectory}/{$css}"),
                    $partialManifest->first(),
                    $manifest,
                ]);

                $tags->push($this->makeTagForChunk(
                    $partialManifest->keys()->first(),
                    $this->assetPath("{$buildDirectory}/{$css}"),
                    $partialManifest->first(),
                    $manifest
                ));
            }
        }

        [$stylesheets, $scripts] = $tags->unique()->partition(fn ($tag) => str_starts_with($tag, '<link'));

        $preloads = $preloads->unique()
            ->sortByDesc(fn ($args) => $this->isCssPath($args[1]))
            ->map(fn ($args) => $this->makePreloadTagForChunk(...$args));

        return new HtmlString($preloads->join('').$stylesheets->join('').$scripts->join(''));
    }

    /**
     * Make tag for the given chunk.
	 * 为给定的块制作标记
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array|null  $chunk
     * @param  array|null  $manifest
     * @return string
     */
    protected function makeTagForChunk($src, $url, $chunk, $manifest)
    {
        if (
            $this->nonce === null
            && $this->integrityKey !== false
            && ! array_key_exists($this->integrityKey, $chunk ?? [])
            && $this->scriptTagAttributesResolvers === []
            && $this->styleTagAttributesResolvers === []) {
            return $this->makeTag($url);
        }

        if ($this->isCssPath($url)) {
            return $this->makeStylesheetTagWithAttributes(
                $url,
                $this->resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)
            );
        }

        return $this->makeScriptTagWithAttributes(
            $url,
            $this->resolveScriptTagAttributes($src, $url, $chunk, $manifest)
        );
    }

    /**
     * Make a preload tag for the given chunk.
	 * 为给定块创建预加载标签
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array  $chunk
     * @param  array  $manifest
     * @return string
     */
    protected function makePreloadTagForChunk($src, $url, $chunk, $manifest)
    {
        $attributes = $this->resolvePreloadTagAttributes($src, $url, $chunk, $manifest);

        if ($attributes === false) {
            return '';
        }

        $this->preloadedAssets[$url] = $this->parseAttributes(
            Collection::make($attributes)->forget('href')->all()
        );

        return '<link '.implode(' ', $this->parseAttributes($attributes)).' />';
    }

    /**
     * Resolve the attributes for the chunks generated script tag.
	 * 解析生成的块脚本标记的属性
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array|null  $chunk
     * @param  array|null  $manifest
     * @return array
     */
    protected function resolveScriptTagAttributes($src, $url, $chunk, $manifest)
    {
        $attributes = $this->integrityKey !== false
            ? ['integrity' => $chunk[$this->integrityKey] ?? false]
            : [];

        foreach ($this->scriptTagAttributesResolvers as $resolver) {
            $attributes = array_merge($attributes, $resolver($src, $url, $chunk, $manifest));
        }

        return $attributes;
    }

    /**
     * Resolve the attributes for the chunks generated stylesheet tag.
	 * 解析块生成的样式表标记的属性
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array|null  $chunk
     * @param  array|null  $manifest
     * @return array
     */
    protected function resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)
    {
        $attributes = $this->integrityKey !== false
            ? ['integrity' => $chunk[$this->integrityKey] ?? false]
            : [];

        foreach ($this->styleTagAttributesResolvers as $resolver) {
            $attributes = array_merge($attributes, $resolver($src, $url, $chunk, $manifest));
        }

        return $attributes;
    }

    /**
     * Resolve the attributes for the chunks generated preload tag.
	 * 解析生成的预加载标记的块的属性
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array  $chunk
     * @param  array  $manifest
     * @return array|false
     */
    protected function resolvePreloadTagAttributes($src, $url, $chunk, $manifest)
    {
        $attributes = $this->isCssPath($url) ? [
            'rel' => 'preload',
            'as' => 'style',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
            'crossorigin' => $this->resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)['crossorigin'] ?? false,
        ] : [
            'rel' => 'modulepreload',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
            'crossorigin' => $this->resolveScriptTagAttributes($src, $url, $chunk, $manifest)['crossorigin'] ?? false,
        ];

        $attributes = $this->integrityKey !== false
            ? array_merge($attributes, ['integrity' => $chunk[$this->integrityKey] ?? false])
            : $attributes;

        foreach ($this->preloadTagAttributesResolvers as $resolver) {
            if (false === ($resolvedAttributes = $resolver($src, $url, $chunk, $manifest))) {
                return false;
            }

            $attributes = array_merge($attributes, $resolvedAttributes);
        }

        return $attributes;
    }

    /**
     * Generate an appropriate tag for the given URL in HMR mode.
	 * 在HMR模式下为给定的URL生成适当的标记
     *
     * @deprecated Will be removed in a future Laravel version.
     *
     * @param  string  $url
     * @return string
     */
    protected function makeTag($url)
    {
        if ($this->isCssPath($url)) {
            return $this->makeStylesheetTag($url);
        }

        return $this->makeScriptTag($url);
    }

    /**
     * Generate a script tag for the given URL.
	 * 为给定的URL生成一个脚本标记
     *
     * @deprecated Will be removed in a future Laravel version.
     *
     * @param  string  $url
     * @return string
     */
    protected function makeScriptTag($url)
    {
        return $this->makeScriptTagWithAttributes($url, []);
    }

    /**
     * Generate a stylesheet tag for the given URL in HMR mode.
	 * 以HMR模式为给定的URL生成一个样式表标记
     *
     * @deprecated Will be removed in a future Laravel version.
     *
     * @param  string  $url
     * @return string
     */
    protected function makeStylesheetTag($url)
    {
        return $this->makeStylesheetTagWithAttributes($url, []);
    }

    /**
     * Generate a script tag with attributes for the given URL.
	 * 为给定的URL生成带有属性的脚本标记
     *
     * @param  string  $url
     * @param  array  $attributes
     * @return string
     */
    protected function makeScriptTagWithAttributes($url, $attributes)
    {
        $attributes = $this->parseAttributes(array_merge([
            'type' => 'module',
            'src' => $url,
            'nonce' => $this->nonce ?? false,
        ], $attributes));

        return '<script '.implode(' ', $attributes).'></script>';
    }

    /**
     * Generate a link tag with attributes for the given URL.
	 * 为给定的URL生成带有属性的链接标记
     *
     * @param  string  $url
     * @param  array  $attributes
     * @return string
     */
    protected function makeStylesheetTagWithAttributes($url, $attributes)
    {
        $attributes = $this->parseAttributes(array_merge([
            'rel' => 'stylesheet',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
        ], $attributes));

        return '<link '.implode(' ', $attributes).' />';
    }

    /**
     * Determine whether the given path is a CSS file.
	 * 确定给定的路径是否为CSS文件
     *
     * @param  string  $path
     * @return bool
     */
    protected function isCssPath($path)
    {
        return preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)$/', $path) === 1;
    }

    /**
     * Parse the attributes into key="value" strings.
	 * 将属性解析为key="value"字符串
     *
     * @param  array  $attributes
     * @return array
     */
    protected function parseAttributes($attributes)
    {
        return Collection::make($attributes)
            ->reject(fn ($value, $key) => in_array($value, [false, null], true))
            ->flatMap(fn ($value, $key) => $value === true ? [$key] : [$key => $value])
            ->map(fn ($value, $key) => is_int($key) ? $value : $key.'="'.$value.'"')
            ->values()
            ->all();
    }

    /**
     * Generate React refresh runtime script.
	 * 生成React刷新运行时脚本
     *
     * @return \Illuminate\Support\HtmlString|void
     */
    public function reactRefresh()
    {
        if (! $this->isRunningHot()) {
            return;
        }

        $attributes = $this->parseAttributes([
            'nonce' => $this->cspNonce(),
        ]);

        return new HtmlString(
            sprintf(
                <<<'HTML'
                <script type="module" %s>
                    import RefreshRuntime from '%s'
                    RefreshRuntime.injectIntoGlobalHook(window)
                    window.$RefreshReg$ = () => {}
                    window.$RefreshSig$ = () => (type) => type
                    window.__vite_plugin_react_preamble_installed__ = true
                </script>
                HTML,
                implode(' ', $attributes),
                $this->hotAsset('@react-refresh')
            )
        );
    }

    /**
     * Get the path to a given asset when running in HMR mode.
	 * 在HMR模式下运行时获取给定资源的路径
     *
     * @return string
     */
    protected function hotAsset($asset)
    {
        return rtrim(file_get_contents($this->hotFile())).'/'.$asset;
    }

    /**
     * Get the URL for an asset.
	 * 获取资源的URL
     *
     * @param  string  $asset
     * @param  string|null  $buildDirectory
     * @return string
     */
    public function asset($asset, $buildDirectory = null)
    {
        $buildDirectory ??= $this->buildDirectory;

        if ($this->isRunningHot()) {
            return $this->hotAsset($asset);
        }

        $chunk = $this->chunk($this->manifest($buildDirectory), $asset);

        return $this->assetPath($buildDirectory.'/'.$chunk['file']);
    }

    /**
     * Generate an asset path for the application.
	 * 为应用程序生成一个资产路径
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    protected function assetPath($path, $secure = null)
    {
        return asset($path, $secure);
    }

    /**
     * Get the the manifest file for the given build directory.
	 * 获取给定构建目录的清单文件
     *
     * @param  string  $buildDirectory
     * @return array
     *
     * @throws \Exception
     */
    protected function manifest($buildDirectory)
    {
        $path = $this->manifestPath($buildDirectory);

        if (! isset(static::$manifests[$path])) {
            if (! is_file($path)) {
                throw new Exception("Vite manifest not found at: {$path}");
            }

            static::$manifests[$path] = json_decode(file_get_contents($path), true);
        }

        return static::$manifests[$path];
    }

    /**
     * Get the path to the manifest file for the given build directory.
	 * 获取给定构建目录的清单文件的路径
     *
     * @param  string  $buildDirectory
     * @return string
     */
    protected function manifestPath($buildDirectory)
    {
        return public_path($buildDirectory.'/'.$this->manifestFilename);
    }

    /**
     * Get a unique hash representing the current manifest, or null if there is no manifest.
	 * 获取表示当前清单的唯一散列，如果没有清单，则为空。
     *
     * @param  string|null  $buildDirectory
     * @return string|null
     */
    public function manifestHash($buildDirectory = null)
    {
        $buildDirectory ??= $this->buildDirectory;

        if ($this->isRunningHot()) {
            return null;
        }

        if (! is_file($path = $this->manifestPath($buildDirectory))) {
            return null;
        }

        return md5_file($path) ?: null;
    }

    /**
     * Get the chunk for the given entry point / asset.
	 * 获取给定入口点/资产的块
     *
     * @param  array  $manifest
     * @param  string  $file
     * @return array
     *
     * @throws \Exception
     */
    protected function chunk($manifest, $file)
    {
        if (! isset($manifest[$file])) {
            throw new Exception("Unable to locate file in Vite manifest: {$file}.");
        }

        return $manifest[$file];
    }

    /**
     * Determine if the HMR server is running.
	 * 确定HMR服务器是否正在运行
     *
     * @return bool
     */
    public function isRunningHot()
    {
        return is_file($this->hotFile());
    }

    /**
     * Get the Vite tag content as a string of HTML.
	 * 以HTML字符串的形式获取Vite标记内容
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->__invoke($this->entryPoints)->toHtml();
    }
}
