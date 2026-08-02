<?php
/**
 * Illuminate，Http，重定向响应
 */

namespace Illuminate\Http;

use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse as BaseRedirectResponse;

class RedirectResponse extends BaseRedirectResponse
{
    use ForwardsCalls, ResponseTrait, Macroable {
        Macroable::__call as macroCall;
    }

    /**
     * The request instance.
	 * 请求实例
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The session store instance.
	 * session 存储实例
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * Flash a piece of data to the session.
	 * 将一段数据写入会话
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $this->session->flash($k, $v);
        }

        return $this;
    }

    /**
     * Add multiple cookies to the response.
	 * 向响应添加多个cookie
     *
     * @param  array  $cookies
     * @return $this
     */
    public function withCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }

        return $this;
    }

    /**
     * Flash an array of input to the session.
	 * 将输入数组闪存到会话中
     *
     * @param  array|null  $input
     * @return $this
     */
    public function withInput(array $input = null)
    {
        $this->session->flashInput($this->removeFilesFromInput(
            ! is_null($input) ? $input : $this->request->input()
        ));

        return $this;
    }

    /**
     * Remove all uploaded files form the given input array.
	 * 从给定的输入数组中删除所有上传的文件
     *
     * @param  array  $input
     * @return array
     */
    protected function removeFilesFromInput(array $input)
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->removeFilesFromInput($value);
            }

            if ($value instanceof SymfonyUploadedFile) {
                unset($input[$key]);
            }
        }

        return $input;
    }

    /**
     * Flash an array of input to the session.
	 * 将输入数组闪现到会话中
     *
     * @return $this
     */
    public function onlyInput()
    {
        return $this->withInput($this->request->only(func_get_args()));
    }

    /**
     * Flash an array of input to the session.
	 * 将输入数组闪现到会话中
     *
     * @return $this
     */
    public function exceptInput()
    {
        return $this->withInput($this->request->except(func_get_args()));
    }

    /**
     * Flash a container of errors to the session.
	 * 将一个错误容器刷到会话中
     *
     * @param  \Illuminate\Contracts\Support\MessageProvider|array|string  $provider
     * @param  string  $key
     * @return $this
     */
    public function withErrors($provider, $key = 'default')
    {
        $value = $this->parseErrors($provider);

        $errors = $this->session->get('errors', new ViewErrorBag);

        if (! $errors instanceof ViewErrorBag) {
            $errors = new ViewErrorBag;
        }

        $this->session->flash(
            'errors', $errors->put($key, $value)
        );

        return $this;
    }

    /**
     * Add a fragment identifier to the URL.
	 * 向URL添加片段标识符
     *
     * @param  string  $fragment
     * @return $this
     */
    public function withFragment($fragment)
    {
        return $this->withoutFragment()
                ->setTargetUrl($this->getTargetUrl().'#'.Str::after($fragment, '#'));
    }

    /**
     * Remove any fragment identifier from the response URL.
	 * 从响应URL中删除任何片段标识符
     *
     * @return $this
     */
    public function withoutFragment()
    {
        return $this->setTargetUrl(Str::before($this->getTargetUrl(), '#'));
    }

    /**
     * Parse the given errors into an appropriate value.
	 * 将给定的错误解析为适当的值
     *
     * @param  \Illuminate\Contracts\Support\MessageProvider|array|string  $provider
     * @return \Illuminate\Support\MessageBag
     */
    protected function parseErrors($provider)
    {
        if ($provider instanceof MessageProvider) {
            return $provider->getMessageBag();
        }

        return new MessageBag((array) $provider);
    }

    /**
     * Get the original response content.
	 * 获取原始响应内容
     *
     * @return null
     */
    public function getOriginalContent()
    {
        //
    }

    /**
     * Get the request instance.
	 * 获取请求实例
     *
     * @return \Illuminate\Http\Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the request instance.
	 * 设置请求实例
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the session store instance.
	 * 获取会话存储实例
     *
     * @return \Illuminate\Session\Store|null
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set the session store instance.
	 * 设置会话存储实例
     *
     * @param  \Illuminate\Session\Store  $session
     * @return void
     */
    public function setSession(SessionStore $session)
    {
        $this->session = $session;
    }

    /**
     * Dynamically bind flash data in the session.
	 * 动态绑定会话中的flash数据
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }

        static::throwBadMethodCallException($method);
    }
}
