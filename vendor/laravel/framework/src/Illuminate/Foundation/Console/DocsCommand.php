<?php
/**
 * Illuminate，基础，控制台，docs 文档命令
 */

namespace Illuminate\Foundation\Console;

use Carbon\CarbonInterval;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

#[AsCommand(name: 'docs')]
class DocsCommand extends Command
{
    /**
     * The name and signature of the console command.
	 * 控制台命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'docs {page? : The documentation page to open} {section? : The section of the page to open}';

    /**
     * The name of the console command.
	 * 控制台命令名称
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'docs';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Access the Laravel documentation';

    /**
     * The console command help text.
	 * 控制台命令帮助文本
     *
     * @var string
     */
    protected $help = 'If you would like to perform a content search against the documention, you may call: <fg=green>php artisan docs -- </><fg=green;options=bold;>search query here</>';

    /**
     * The HTTP client instance.
	 * HTTP客户端实例
     *
     * @var \Illuminate\Http\Client\Factory
     */
    protected $http;

    /**
     * The cache repository implementation.
	 * 缓存存储库实现
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * The custom URL opener.
	 * 自定义URL打开器
     *
     * @var callable|null
     */
    protected $urlOpener;

    /**
     * The custom documentation version to open.
	 * 要打开的自定义文档版本
     *
     * @var string|null
     */
    protected $version;

    /**
     * The operating system family.
	 * 操作系统家族
     *
     * @var string
     */
    protected $systemOsFamily = PHP_OS_FAMILY;

    /**
     * Configure the current command.
	 * 配置当前命令
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        if ($this->isSearching()) {
            $this->ignoreValidationErrors();
        }
    }

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @param  \Illuminate\Http\Client\Factory  $http
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return int
     */
    public function handle(Http $http, Cache $cache)
    {
        $this->http = $http;
        $this->cache = $cache;

        try {
            $this->openUrl();
        } catch (ProcessFailedException $e) {
            if ($e->getProcess()->getExitCodeText() === 'Interrupt') {
                return $e->getProcess()->getExitCode();
            }

            throw $e;
        }

        $this->refreshDocs();

        return Command::SUCCESS;
    }

    /**
     * Open the documentation URL.
	 * 打开文档URL
     *
     * @return void
     */
    protected function openUrl()
    {
        with($this->url(), function ($url) {
            $this->components->info("Opening the docs to: <fg=yellow>{$url}</>");

            $this->open($url);
        });
    }

    /**
     * The URL to the documentation page.
	 * 文档页面的URL
     *
     * @return string
     */
    protected function url()
    {
        if ($this->isSearching()) {
            return "https://laravel.com/docs/{$this->version()}?".Arr::query([
                'q' => $this->searchQuery(),
            ]);
        }

        return with($this->page(), function ($page) {
            return trim("https://laravel.com/docs/{$this->version()}/{$page}#{$this->section($page)}", '#/');
        });
    }

    /**
     * The page the user is opening.
	 * 用户正在打开的页面
     *
     * @return string
     */
    protected function page()
    {
        return with($this->resolvePage(), function ($page) {
            if ($page === null) {
                $this->components->warn('Unable to determine the page you are trying to visit.');

                return '/';
            }

            return $page;
        });
    }

    /**
     * Determine the page to open.
	 * 确定要打开的页面
     *
     * @return string|null
     */
    protected function resolvePage()
    {
        if ($this->option('no-interaction') && $this->didNotRequestPage()) {
            return '/';
        }

        return $this->didNotRequestPage()
            ? $this->askForPage()
            : $this->guessPage();
    }

    /**
     * Determine if the user requested a specific page when calling the command.
	 * 确定用户在调用命令时是否请求了特定的页面
     *
     * @return bool
     */
    protected function didNotRequestPage()
    {
        return $this->argument('page') === null;
    }

    /**
     * Ask the user which page they would like to open.
	 * 询问用户他们想打开哪个页面
     *
     * @return string|null
     */
    protected function askForPage()
    {
        return $this->askForPageViaCustomStrategy() ?? $this->askForPageViaAutocomplete();
    }

    /**
     * Ask the user which page they would like to open via a custom strategy.
	 * 询问用户他们希望通过自定义策略打开哪个页面
     *
     * @return string|null
     */
    protected function askForPageViaCustomStrategy()
    {
        try {
            $strategy = require Env::get('ARTISAN_DOCS_ASK_STRATEGY');
        } catch (Throwable $e) {
            return null;
        }

        if (! is_callable($strategy)) {
            return null;
        }

        return $strategy($this) ?? '/';
    }

    /**
     * Ask the user which page they would like to open using autocomplete.
	 * 询问用户他们希望使用自动完成打开哪个页面
     *
     * @return string|null
     */
    protected function askForPageViaAutocomplete()
    {
        $choice = $this->components->choice(
            'Which page would you like to open?',
            $this->pages()->mapWithKeys(fn ($option) => [
                Str::lower($option['title']) => $option['title'],
            ])->all(),
            'installation',
            3
        );

        return $this->pages()->filter(
            fn ($page) => $page['title'] === $choice || Str::lower($page['title']) === $choice
        )->keys()->first() ?: null;
    }

    /**
     * Guess the page the user is attempting to open.
	 * 猜测用户试图打开的页面
     *
     * @return string|null
     */
    protected function guessPage()
    {
        return $this->pages()
            ->filter(fn ($page) => str_starts_with(
                Str::slug($page['title'], ' '),
                Str::slug($this->argument('page'), ' ')
            ))->keys()->first() ?? $this->pages()->map(fn ($page) => similar_text(
                Str::slug($page['title'], ' '),
                Str::slug($this->argument('page'), ' '),
            ))
            ->filter(fn ($score) => $score >= min(3, Str::length($this->argument('page'))))
            ->sortDesc()
            ->keys()
            ->sortByDesc(fn ($slug) => Str::contains(
                Str::slug($this->pages()[$slug]['title'], ' '),
                Str::slug($this->argument('page'), ' ')
            ) ? 1 : 0)
            ->first();
    }

    /**
     * The section the user specifically asked to open.
	 * 用户特别要求打开的部分
     *
     * @param  string  $page
     * @return string|null
     */
    protected function section($page)
    {
        return $this->didNotRequestSection()
            ? null
            : $this->guessSection($page);
    }

    /**
     * Determine if the user requested a specific section when calling the command.
	 * 确定用户在调用命令时是否请求了特定的部分
     *
     * @return bool
     */
    protected function didNotRequestSection()
    {
        return $this->argument('section') === null;
    }

    /**
     * Guess the section the user is attempting to open.
	 * 猜测用户试图打开的部分
     *
     * @param  string  $page
     * @return string|null
     */
    protected function guessSection($page)
    {
        return $this->sectionsFor($page)
            ->filter(fn ($section) => str_starts_with(
                Str::slug($section['title'], ' '),
                Str::slug($this->argument('section'), ' ')
            ))->keys()->first() ?? $this->sectionsFor($page)->map(fn ($section) => similar_text(
                Str::slug($section['title'], ' '),
                Str::slug($this->argument('section'), ' '),
            ))
            ->filter(fn ($score) => $score >= min(3, Str::length($this->argument('section'))))
            ->sortDesc()
            ->keys()
            ->sortByDesc(fn ($slug) => Str::contains(
                Str::slug($this->sectionsFor($page)[$slug]['title'], ' '),
                Str::slug($this->argument('section'), ' ')
            ) ? 1 : 0)
            ->first();
    }

    /**
     * Open the URL in the user's browser.
	 * 在用户的浏览器中打开URL
     *
     * @param  string  $url
     * @return void
     */
    protected function open($url)
    {
        ($this->urlOpener ?? function ($url) {
            if (Env::get('ARTISAN_DOCS_OPEN_STRATEGY')) {
                $this->openViaCustomStrategy($url);
            } elseif (in_array($this->systemOsFamily, ['Darwin', 'Windows', 'Linux'])) {
                $this->openViaBuiltInStrategy($url);
            } else {
                $this->components->warn('Unable to open the URL on your system. You will need to open it yourself or create a custom opener for your system.');
            }
        })($url);
    }

    /**
     * Open the URL via a custom strategy.
	 * 通过自定义策略打开URL
     *
     * @param  string  $url
     * @return void
     */
    protected function openViaCustomStrategy($url)
    {
        try {
            $command = require Env::get('ARTISAN_DOCS_OPEN_STRATEGY');
        } catch (Throwable $e) {
            $command = null;
        }

        if (! is_callable($command)) {
            $this->components->warn('Unable to open the URL with your custom strategy. You will need to open it yourself.');

            return;
        }

        $command($url);
    }

    /**
     * Open the URL via the built in strategy.
	 * 通过内置策略打开URL
     *
     * @param  string  $url
     * @return void
     */
    protected function openViaBuiltInStrategy($url)
    {
        if ($this->systemOsFamily === 'Windows') {
            $process = tap(Process::fromShellCommandline(escapeshellcmd("start {$url}")))->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return;
        }

        $binary = Collection::make(match ($this->systemOsFamily) {
            'Darwin' => ['open'],
            'Linux' => ['xdg-open', 'wslview'],
        })->first(fn ($binary) => (new ExecutableFinder)->find($binary) !== null);

        if ($binary === null) {
            $this->components->warn('Unable to open the URL on your system. You will need to open it yourself or create a custom opener for your system.');

            return;
        }

        $process = tap(Process::fromShellCommandline(escapeshellcmd("{$binary} {$url}")))->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * The available sections for the page.
	 * 页面可用的部分
     *
     * @param  string  $page
     * @return \Illuminate\Support\Collection
     */
    public function sectionsFor($page)
    {
        return new Collection($this->pages()[$page]['sections']);
    }

    /**
     * The pages available to open.
	 * 可供打开的页面
     *
     * @return \Illuminate\Support\Collection
     */
    public function pages()
    {
        return new Collection($this->docs()['pages']);
    }

    /**
     * Get the documentation index as a collection.
	 * 获取文档索引作为集合
     *
     * @return \Illuminate\Support\Collection
     */
    public function docs()
    {
        return $this->cache->remember(
            "artisan.docs.{{$this->version()}}.index",
            CarbonInterval::months(2),
            fn () => $this->fetchDocs()->throw()->collect()
        );
    }

    /**
     * Refresh the cached copy of the documentation index.
	 * 刷新文档索引的缓存副本
     *
     * @return void
     */
    protected function refreshDocs()
    {
        with($this->fetchDocs(), function ($response) {
            if ($response->successful()) {
                $this->cache->put("artisan.docs.{{$this->version()}}.index", $response->collect(), CarbonInterval::months(2));
            }
        });
    }

    /**
     * Fetch the documentation index from the Laravel website.
	 * 从Laravel网站获取文档索引
     *
     * @return \Illuminate\Http\Client\Response
     */
    protected function fetchDocs()
    {
        return $this->http->get("https://laravel.com/docs/{$this->version()}/index.json");
    }

    /**
     * Determine the version of the docs to open.
	 * 确定要打开的文档的版本
     *
     * @return string
     */
    protected function version()
    {
        return Str::before($this->version ?? $this->laravel->version(), '.').'.x';
    }

    /**
     * The search query the user provided.
	 * 用户提供的搜索查询
     *
     * @return string
     */
    protected function searchQuery()
    {
        return Collection::make($_SERVER['argv'])->skip(3)->implode(' ');
    }

    /**
     * Determine if the command is intended to perform a search.
	 * 确定该命令是否打算执行搜索
     *
     * @return bool
     */
    protected function isSearching()
    {
        return ($_SERVER['argv'][2] ?? null) === '--';
    }

    /**
     * Set the documentation version.
	 * 设置文档版本
     *
     * @param  string  $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set a custom URL opener.
	 * 设置自定义URL打开器
     *
     * @param  callable|null  $opener
     * @return $this
     */
    public function setUrlOpener($opener)
    {
        $this->urlOpener = $opener;

        return $this;
    }

    /**
     * Set the system operating system family.
	 * 设置系统操作系统家族
     *
     * @param  string  $family
     * @return $this
     */
    public function setSystemOsFamily($family)
    {
        $this->systemOsFamily = $family;

        return $this;
    }
}
