<?php
/**
 * GuzzleHttp，Cookie，文件 Cookie 压缩
 */

namespace GuzzleHttp\Cookie;

use GuzzleHttp\Utils;

/**
 * Persists non-session cookies using a JSON formatted file
 * 使用JSON格式文件持久化非会话cookie。
 */
class FileCookieJar extends CookieJar
{
    /**
     * @var string filename
     */
    private $filename;

    /**
     * @var bool Control whether to persist session cookies or not.
     */
    private $storeSessionCookies;

    /**
     * Create a new FileCookieJar object
	 * 创建一个新的FileCookieJar对象
     *
     * @param string $cookieFile          File to store the cookie data
     * @param bool   $storeSessionCookies Set to true to store session cookies
     *                                    in the cookie jar.
     *
     * @throws \RuntimeException if the file cannot be found or created
     */
    public function __construct(string $cookieFile, bool $storeSessionCookies = false)
    {
        parent::__construct();
        $this->filename = $cookieFile;
        $this->storeSessionCookies = $storeSessionCookies;

        if (\file_exists($cookieFile)) {
            $this->load($cookieFile);
        }
    }

    /**
     * Saves the file when shutting down
	 * 关闭时保存文件
     */
    public function __destruct()
    {
        $this->save($this->filename);
    }

    /**
     * Saves the cookies to a file.
	 * 将cookie保存到一个文件
     *
     * @param string $filename File to save
     *
     * @throws \RuntimeException if the file cannot be found or created
     */
    public function save(string $filename): void
    {
        $json = [];
        /** @var SetCookie $cookie */
        foreach ($this as $cookie) {
            if (CookieJar::shouldPersist($cookie, $this->storeSessionCookies)) {
                $json[] = $cookie->toArray();
            }
        }

        $jsonStr = Utils::jsonEncode($json);
        if (false === \file_put_contents($filename, $jsonStr, \LOCK_EX)) {
            throw new \RuntimeException("Unable to save file {$filename}");
        }
    }

    /**
     * Load cookies from a JSON formatted file.
	 * 从JSON格式的文件中加载cookie。
     *
     * Old cookies are kept unless overwritten by newly loaded ones.
     *
     * @param string $filename Cookie file to load.
     *
     * @throws \RuntimeException if the file cannot be loaded.
     */
    public function load(string $filename): void
    {
        $json = \file_get_contents($filename);
        if (false === $json) {
            throw new \RuntimeException("Unable to load file {$filename}");
        }
        if ($json === '') {
            return;
        }

        $data = Utils::jsonDecode($json, true);
        if (\is_array($data)) {
            foreach ($data as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (\is_scalar($data) && !empty($data)) {
            throw new \RuntimeException("Invalid cookie file: {$filename}");
        }
    }
}
