<?php
/**
 * GuzzleHttp，Cookie，会话 Cookie 压缩
 */

namespace GuzzleHttp\Cookie;

/**
 * Persists cookies in the client session
 * 在客户端会话中持久化cookie
 */
class SessionCookieJar extends CookieJar
{
    /**
     * @var string session key
     */
    private $sessionKey;

    /**
     * @var bool Control whether to persist session cookies or not.
	 * 控制是否坚持会话cookie
     */
    private $storeSessionCookies;

    /**
     * Create a new SessionCookieJar object
	 * 创建一个新的SessionCookieJar对象
     *
     * @param string $sessionKey          Session key name to store the cookie
     *                                    data in session
     * @param bool   $storeSessionCookies Set to true to store session cookies
     *                                    in the cookie jar.
     */
    public function __construct(string $sessionKey, bool $storeSessionCookies = false)
    {
        parent::__construct();
        $this->sessionKey = $sessionKey;
        $this->storeSessionCookies = $storeSessionCookies;
        $this->load();
    }

    /**
     * Saves cookies to session when shutting down
	 * 关闭时将cookie保存到会话中
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Save cookies to the client session
	 * 将cookie保存到客户端会话
     */
    public function save(): void
    {
        $json = [];
        /** @var SetCookie $cookie */
        foreach ($this as $cookie) {
            if (CookieJar::shouldPersist($cookie, $this->storeSessionCookies)) {
                $json[] = $cookie->toArray();
            }
        }

        $_SESSION[$this->sessionKey] = \json_encode($json);
    }

    /**
     * Load the contents of the client session into the data array
	 * 将客户端会话的内容加载到数据数组中
     */
    protected function load(): void
    {
        if (!isset($_SESSION[$this->sessionKey])) {
            return;
        }
        $data = \json_decode($_SESSION[$this->sessionKey], true);
        if (\is_array($data)) {
            foreach ($data as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (\strlen($data)) {
            throw new \RuntimeException('Invalid cookie data');
        }
    }
}
