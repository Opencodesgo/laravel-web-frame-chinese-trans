<?php
/**
 * Illuminate, 测试, 测试响应
 */

namespace Illuminate\Testing;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\Constraints\SeeInOrder;
use Illuminate\Testing\Fluent\AssertableJson;
use LogicException;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @mixin \Illuminate\Http\Response
 */
class TestResponse implements ArrayAccess
{
    use Concerns\AssertsStatusCodes, Tappable, Macroable {
        __call as macroCall;
    }

    /**
     * The response to delegate to.
	 * 委托的响应
     *
     * @var \Illuminate\Http\Response
     */
    public $baseResponse;

    /**
     * The collection of logged exceptions for the request.
	 * 请求的记录异常的集合
     *
     * @var \Illuminate\Support\Collection
     */
    public $exceptions;

    /**
     * The streamed content of the response.
	 * 响应的流内容
     *
     * @var string
     */
    protected $streamedContent;

    /**
     * Create a new test response instance.
	 * 创建一个新的测试响应实例
     *
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function __construct($response)
    {
        $this->baseResponse = $response;
        $this->exceptions = new Collection;
    }

    /**
     * Create a new TestResponse from another response.
	 * 从另一个响应创建一个新的TestResponse
     *
     * @param  \Illuminate\Http\Response  $response
     * @return static
     */
    public static function fromBaseResponse($response)
    {
        return new static($response);
    }

    /**
     * Assert that the response has a successful status code.
	 * 断言响应具有成功状态码
     *
     * @return $this
     */
    public function assertSuccessful()
    {
        PHPUnit::assertTrue(
            $this->isSuccessful(),
            $this->statusMessageWithDetails('>=200, <300', $this->getStatusCode())
        );

        return $this;
    }

    /**
     * Assert that the response is a server error.
	 * 断言响应是服务器错误
     *
     * @return $this
     */
    public function assertServerError()
    {
        PHPUnit::assertTrue(
            $this->isServerError(),
            $this->statusMessageWithDetails('>=500, < 600', $this->getStatusCode())
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
	 * 断言响应具有给定的状态码
     *
     * @param  int  $status
     * @return $this
     */
    public function assertStatus($status)
    {
        $message = $this->statusMessageWithDetails($status, $actual = $this->getStatusCode());

        PHPUnit::assertSame($actual, $status, $message);

        return $this;
    }

    /**
     * Get an assertion message for a status assertion containing extra details when available.
	 * 获取状态断言的断言消息，其中包含可用时的额外详细信息。
     *
     * @param  string|int  $expected
     * @param  string|int  $actual
     * @return string
     */
    protected function statusMessageWithDetails($expected, $actual)
    {
        return "Expected response status code [{$expected}] but received {$actual}.";
    }

    /**
     * Assert whether the response is redirecting to a given URI.
	 * 断言响应是否重定向到给定的URI
     *
     * @param  string|null  $uri
     * @return $this
     */
    public function assertRedirect($uri = null)
    {
        PHPUnit::assertTrue(
            $this->isRedirect(),
            $this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
        );

        if (! is_null($uri)) {
            $this->assertLocation($uri);
        }

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a URI that contains the given URI.
	 * 断言响应是否重定向到包含给定URI的URI
     *
     * @param  string  $uri
     * @return $this
     */
    public function assertRedirectContains($uri)
    {
        PHPUnit::assertTrue(
            $this->isRedirect(),
            $this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
        );

        PHPUnit::assertTrue(
            Str::contains($this->headers->get('Location'), $uri), 'Redirect location ['.$this->headers->get('Location').'] does not contain ['.$uri.'].'
        );

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a given route.
	 * 断言响应是否被重定向到给定的路由
     *
     * @param  string  $name
     * @param  mixed  $parameters
     * @return $this
     */
    public function assertRedirectToRoute($name, $parameters = [])
    {
        $uri = route($name, $parameters);

        PHPUnit::assertTrue(
            $this->isRedirect(),
            $this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
        );

        $request = Request::create($this->headers->get('Location'));

        PHPUnit::assertEquals(
            app('url')->to($uri), $request->fullUrl()
        );

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a given signed route.
	 * 断言响应是否重定向到给定的签名路由
     *
     * @param  string|null  $name
     * @param  mixed  $parameters
     * @return $this
     */
    public function assertRedirectToSignedRoute($name = null, $parameters = [])
    {
        if (! is_null($name)) {
            $uri = route($name, $parameters);
        }

        PHPUnit::assertTrue(
            $this->isRedirect(),
            $this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
        );

        $request = Request::create($this->headers->get('Location'));

        PHPUnit::assertTrue(
            $request->hasValidSignature(), 'The response is not a redirect to a signed route.'
        );

        if (! is_null($name)) {
            $expectedUri = rtrim($request->fullUrlWithQuery([
                'signature' => null,
                'expires' => null,
            ]), '?');

            PHPUnit::assertEquals(
                app('url')->to($uri), $expectedUri
            );
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
	 * 断言响应包含给定的标头并等于可选值
     *
     * @param  string  $headerName
     * @param  mixed  $value
     * @return $this
     */
    public function assertHeader($headerName, $value = null)
    {
        PHPUnit::assertTrue(
            $this->headers->has($headerName), "Header [{$headerName}] not present on response."
        );

        $actual = $this->headers->get($headerName);

        if (! is_null($value)) {
            PHPUnit::assertEquals(
                $value, $this->headers->get($headerName),
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that the response does not contain the given header.
	 * 断言响应不包含给定的标头
     *
     * @param  string  $headerName
     * @return $this
     */
    public function assertHeaderMissing($headerName)
    {
        PHPUnit::assertFalse(
            $this->headers->has($headerName), "Unexpected header [{$headerName}] is present on response."
        );

        return $this;
    }

    /**
     * Assert that the current location header matches the given URI.
	 * 断言当前位置标头与给定的URI匹配
     *
     * @param  string  $uri
     * @return $this
     */
    public function assertLocation($uri)
    {
        PHPUnit::assertEquals(
            app('url')->to($uri), app('url')->to($this->headers->get('Location'))
        );

        return $this;
    }

    /**
     * Assert that the response offers a file download.
	 * 断言响应提供文件下载
     *
     * @param  string|null  $filename
     * @return $this
     */
    public function assertDownload($filename = null)
    {
        $contentDisposition = explode(';', $this->headers->get('content-disposition'));

        if (trim($contentDisposition[0]) !== 'attachment') {
            PHPUnit::fail(
                'Response does not offer a file download.'.PHP_EOL.
                'Disposition ['.trim($contentDisposition[0]).'] found in header, [attachment] expected.'
            );
        }

        if (! is_null($filename)) {
            if (isset($contentDisposition[1]) &&
                trim(explode('=', $contentDisposition[1])[0]) !== 'filename') {
                PHPUnit::fail(
                    'Unsupported Content-Disposition header provided.'.PHP_EOL.
                    'Disposition ['.trim(explode('=', $contentDisposition[1])[0]).'] found in header, [filename] expected.'
                );
            }

            $message = "Expected file [{$filename}] is not present in Content-Disposition header.";

            if (! isset($contentDisposition[1])) {
                PHPUnit::fail($message);
            } else {
                PHPUnit::assertSame(
                    $filename,
                    isset(explode('=', $contentDisposition[1])[1])
                        ? trim(explode('=', $contentDisposition[1])[1], " \"'")
                        : '',
                    $message
                );

                return $this;
            }
        } else {
            PHPUnit::assertTrue(true);

            return $this;
        }
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
	 * 断言响应包含给定的cookie并等于可选值
     *
     * @param  string  $cookieName
     * @param  mixed  $value
     * @return $this
     */
    public function assertPlainCookie($cookieName, $value = null)
    {
        $this->assertCookie($cookieName, $value, false);

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
	 * 断言响应包含给定的cookie并等于可选值
     *
     * @param  string  $cookieName
     * @param  mixed  $value
     * @param  bool  $encrypted
     * @param  bool  $unserialize
     * @return $this
     */
    public function assertCookie($cookieName, $value = null, $encrypted = true, $unserialize = false)
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName, $encrypted && ! is_null($value), $unserialize),
            "Cookie [{$cookieName}] not present on response."
        );

        if (! $cookie || is_null($value)) {
            return $this;
        }

        $cookieValue = $cookie->getValue();

        PHPUnit::assertEquals(
            $value, $cookieValue,
            "Cookie [{$cookieName}] was found, but value [{$cookieValue}] does not match [{$value}]."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and is expired.
	 * 断言响应包含给定的cookie并且已经过期
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieExpired($cookieName)
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName, false),
            "Cookie [{$cookieName}] not present on response."
        );

        $expiresAt = Carbon::createFromTimestamp($cookie->getExpiresTime());

        PHPUnit::assertTrue(
            $cookie->getExpiresTime() !== 0 && $expiresAt->lessThan(Carbon::now()),
            "Cookie [{$cookieName}] is not expired, it expires at [{$expiresAt}]."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and is not expired.
	 * 断言响应包含给定的cookie并且没有过期
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieNotExpired($cookieName)
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName, false),
            "Cookie [{$cookieName}] not present on response."
        );

        $expiresAt = Carbon::createFromTimestamp($cookie->getExpiresTime());

        PHPUnit::assertTrue(
            $cookie->getExpiresTime() === 0 || $expiresAt->greaterThan(Carbon::now()),
            "Cookie [{$cookieName}] is expired, it expired at [{$expiresAt}]."
        );

        return $this;
    }

    /**
     * Asserts that the response does not contain the given cookie.
	 * 断言响应不包含给定的cookie
     *
     * @param  string  $cookieName
     * @return $this
     */
    public function assertCookieMissing($cookieName)
    {
        PHPUnit::assertNull(
            $this->getCookie($cookieName, false),
            "Cookie [{$cookieName}] is present on response."
        );

        return $this;
    }

    /**
     * Get the given cookie from the response.
	 * 从响应中获取给定的cookie
     *
     * @param  string  $cookieName
     * @param  bool  $decrypt
     * @param  bool  $unserialize
     * @return \Symfony\Component\HttpFoundation\Cookie|null
     */
    public function getCookie($cookieName, $decrypt = true, $unserialize = false)
    {
        foreach ($this->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                if (! $decrypt) {
                    return $cookie;
                }

                $decryptedValue = CookieValuePrefix::remove(
                    app('encrypter')->decrypt($cookie->getValue(), $unserialize)
                );

                return new Cookie(
                    $cookie->getName(),
                    $decryptedValue,
                    $cookie->getExpiresTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly(),
                    $cookie->isRaw(),
                    $cookie->getSameSite()
                );
            }
        }
    }

    /**
     * Assert that the given string matches the response content.
	 * 断言给定的字符串与响应内容匹配
     *
     * @param  string  $value
     * @return $this
     */
    public function assertContent($value)
    {
        PHPUnit::assertSame($value, $this->content());

        return $this;
    }

    /**
     * Assert that the given string matches the streamed response content.
	 * 断言给定的字符串与流响应内容匹配
     *
     * @param  string  $value
     * @return $this
     */
    public function assertStreamedContent($value)
    {
        PHPUnit::assertSame($value, $this->streamedContent());

        return $this;
    }

    /**
     * Assert that the given string or array of strings are contained within the response.
	 * 断言给定的字符串或字符串数组包含在响应中
     *
     * @param  string|array  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertSee($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map('e', $value) : $value;

        foreach ($values as $value) {
            PHPUnit::assertStringContainsString((string) $value, $this->getContent());
        }

        return $this;
    }

    /**
     * Assert that the given strings are contained in order within the response.
	 * 断言给定的字符串按顺序包含在响应中
     *
     * @param  array  $values
     * @param  bool  $escape
     * @return $this
     */
    public function assertSeeInOrder(array $values, $escape = true)
    {
        $values = $escape ? array_map('e', $values) : $values;

        PHPUnit::assertThat($values, new SeeInOrder($this->getContent()));

        return $this;
    }

    /**
     * Assert that the given string or array of strings are contained within the response text.
	 * 断言给定的字符串或字符串数组包含在响应文本中
     *
     * @param  string|array  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertSeeText($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map('e', $value) : $value;

        $content = strip_tags($this->getContent());

        foreach ($values as $value) {
            PHPUnit::assertStringContainsString((string) $value, $content);
        }

        return $this;
    }

    /**
     * Assert that the given strings are contained in order within the response text.
	 * 断言给定的字符串按顺序包含在响应文本中
     *
     * @param  array  $values
     * @param  bool  $escape
     * @return $this
     */
    public function assertSeeTextInOrder(array $values, $escape = true)
    {
        $values = $escape ? array_map('e', $values) : $values;

        PHPUnit::assertThat($values, new SeeInOrder(strip_tags($this->getContent())));

        return $this;
    }

    /**
     * Assert that the given string or array of strings are not contained within the response.
	 * 断言给定的字符串或字符串数组不包含在响应中
     *
     * @param  string|array  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertDontSee($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map('e', $value) : $value;

        foreach ($values as $value) {
            PHPUnit::assertStringNotContainsString((string) $value, $this->getContent());
        }

        return $this;
    }

    /**
     * Assert that the given string or array of strings are not contained within the response text.
	 * 断言给定的字符串或字符串数组不包含在响应文本中
     *
     * @param  string|array  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertDontSeeText($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map('e', $value) : $value;

        $content = strip_tags($this->getContent());

        foreach ($values as $value) {
            PHPUnit::assertStringNotContainsString((string) $value, $content);
        }

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
	 * 断言响应是给定JSON的超集
     *
     * @param  array|callable  $value
     * @param  bool  $strict
     * @return $this
     */
    public function assertJson($value, $strict = false)
    {
        $json = $this->decodeResponseJson();

        if (is_array($value)) {
            $json->assertSubset($value, $strict);
        } else {
            $assert = AssertableJson::fromAssertableJsonString($json);

            $value($assert);

            if (Arr::isAssoc($assert->toArray())) {
                $assert->interacted();
            }
        }

        return $this;
    }

    /**
     * Assert that the expected value and type exists at the given path in the response.
	 * 断言期望的值和类型存在于响应中的给定路径中
     *
     * @param  string  $path
     * @param  mixed  $expect
     * @return $this
     */
    public function assertJsonPath($path, $expect)
    {
        $this->decodeResponseJson()->assertPath($path, $expect);

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
	 * 断言响应具有确切给定的JSON
     *
     * @param  array  $data
     * @return $this
     */
    public function assertExactJson(array $data)
    {
        $this->decodeResponseJson()->assertExact($data);

        return $this;
    }

    /**
     * Assert that the response has the similar JSON as given.
	 * 断言响应具有与给定的类似的JSON
     *
     * @param  array  $data
     * @return $this
     */
    public function assertSimilarJson(array $data)
    {
        $this->decodeResponseJson()->assertSimilar($data);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
	 * 断言响应包含给定的JSON片段
     *
     * @param  array  $data
     * @return $this
     */
    public function assertJsonFragment(array $data)
    {
        $this->decodeResponseJson()->assertFragment($data);

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
	 * 断言响应不包含给定的JSON片段
     *
     * @param  array  $data
     * @param  bool  $exact
     * @return $this
     */
    public function assertJsonMissing(array $data, $exact = false)
    {
        $this->decodeResponseJson()->assertMissing($data, $exact);

        return $this;
    }

    /**
     * Assert that the response does not contain the exact JSON fragment.
	 * 断言响应不包含确切的JSON片段
     *
     * @param  array  $data
     * @return $this
     */
    public function assertJsonMissingExact(array $data)
    {
        $this->decodeResponseJson()->assertMissingExact($data);

        return $this;
    }

    /**
     * Assert that the response does not contain the given path.
	 * 断言响应不包含给定的路径
     *
     * @param  string  $path
     * @return $this
     */
    public function assertJsonMissingPath(string $path)
    {
        $this->decodeResponseJson()->assertMissingPath($path);

        return $this;
    }

    /**
     * Assert that the response has a given JSON structure.
	 * 断言响应具有给定的JSON结构
     *
     * @param  array|null  $structure
     * @param  array|null  $responseData
     * @return $this
     */
    public function assertJsonStructure(array $structure = null, $responseData = null)
    {
        $this->decodeResponseJson()->assertStructure($structure, $responseData);

        return $this;
    }

    /**
     * Assert that the response JSON has the expected count of items at the given key.
	 * 断言响应JSON具有给定键处的预期项计数
     *
     * @param  int  $count
     * @param  string|null  $key
     * @return $this
     */
    public function assertJsonCount(int $count, $key = null)
    {
        $this->decodeResponseJson()->assertCount($count, $key);

        return $this;
    }

    /**
     * Assert that the response has the given JSON validation errors.
	 * 断言响应具有给定的JSON验证错误
     *
     * @param  string|array  $errors
     * @param  string  $responseKey
     * @return $this
     */
    public function assertJsonValidationErrors($errors, $responseKey = 'errors')
    {
        $errors = Arr::wrap($errors);

        PHPUnit::assertNotEmpty($errors, 'No validation errors were provided.');

        $jsonErrors = Arr::get($this->json(), $responseKey) ?? [];

        $errorMessage = $jsonErrors
                ? 'Response has the following JSON validation errors:'.
                        PHP_EOL.PHP_EOL.json_encode($jsonErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
                : 'Response does not have JSON validation errors.';

        foreach ($errors as $key => $value) {
            if (is_int($key)) {
                $this->assertJsonValidationErrorFor($value, $responseKey);

                continue;
            }

            $this->assertJsonValidationErrorFor($key, $responseKey);

            foreach (Arr::wrap($value) as $expectedMessage) {
                $errorMissing = true;

                foreach (Arr::wrap($jsonErrors[$key]) as $jsonErrorMessage) {
                    if (Str::contains($jsonErrorMessage, $expectedMessage)) {
                        $errorMissing = false;

                        break;
                    }
                }
            }

            if ($errorMissing) {
                PHPUnit::fail(
                    "Failed to find a validation error in the response for key and message: '$key' => '$expectedMessage'".PHP_EOL.PHP_EOL.$errorMessage
                );
            }
        }

        return $this;
    }

    /**
     * Assert the response has any JSON validation errors for the given key.
	 * 断言响应对于给定的键有任何JSON验证错误
     *
     * @param  string  $key
     * @param  string  $responseKey
     * @return $this
     */
    public function assertJsonValidationErrorFor($key, $responseKey = 'errors')
    {
        $jsonErrors = Arr::get($this->json(), $responseKey) ?? [];

        $errorMessage = $jsonErrors
            ? 'Response has the following JSON validation errors:'.
            PHP_EOL.PHP_EOL.json_encode($jsonErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
            : 'Response does not have JSON validation errors.';

        PHPUnit::assertArrayHasKey(
            $key,
            $jsonErrors,
            "Failed to find a validation error in the response for key: '{$key}'".PHP_EOL.PHP_EOL.$errorMessage
        );

        return $this;
    }

    /**
     * Assert that the response has no JSON validation errors for the given keys.
	 * 断言响应对于给定的键没有JSON验证错误
     *
     * @param  string|array|null  $keys
     * @param  string  $responseKey
     * @return $this
     */
    public function assertJsonMissingValidationErrors($keys = null, $responseKey = 'errors')
    {
        if ($this->getContent() === '') {
            PHPUnit::assertTrue(true);

            return $this;
        }

        $json = $this->json();

        if (! Arr::has($json, $responseKey)) {
            PHPUnit::assertTrue(true);

            return $this;
        }

        $errors = Arr::get($json, $responseKey, []);

        if (is_null($keys) && count($errors) > 0) {
            PHPUnit::fail(
                'Response has unexpected validation errors: '.PHP_EOL.PHP_EOL.
                json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
        }

        foreach (Arr::wrap($keys) as $key) {
            PHPUnit::assertFalse(
                isset($errors[$key]),
                "Found unexpected validation error for key: '{$key}'"
            );
        }

        return $this;
    }

    /**
     * Assert that the given key is a JSON array.
	 * 断言给定的键是一个JSON数组
     *
     * @param  string|null  $key
     * @return $this
     */
    public function assertJsonIsArray($key = null)
    {
        $data = $this->json($key);

        $encodedData = json_encode($data);

        PHPUnit::assertTrue(
            is_array($data)
            && str_starts_with($encodedData, '[')
            && str_ends_with($encodedData, ']')
        );

        return $this;
    }

    /**
     * Assert that the given key is a JSON object.
	 * 断言给定的键是一个JSON对象
     *
     * @param  string|null  $key
     * @return $this
     */
    public function assertJsonIsObject($key = null)
    {
        $data = $this->json($key);

        $encodedData = json_encode($data);

        PHPUnit::assertTrue(
            is_array($data)
            && str_starts_with($encodedData, '{')
            && str_ends_with($encodedData, '}')
        );

        return $this;
    }

    /**
     * Validate and return the decoded response JSON.
	 * 验证并返回解码后的响应JSON
     *
     * @return \Illuminate\Testing\AssertableJsonString
     *
     * @throws \Throwable
     */
    public function decodeResponseJson()
    {
        $testJson = new AssertableJsonString($this->getContent());

        $decodedResponse = $testJson->json();

        if (is_null($decodedResponse) || $decodedResponse === false) {
            if ($this->exception) {
                throw $this->exception;
            } else {
                PHPUnit::fail('Invalid JSON was returned from the route.');
            }
        }

        return $testJson;
    }

    /**
     * Validate and return the decoded response JSON.
	 * 验证并返回解码后的响应JSON
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function json($key = null)
    {
        return $this->decodeResponseJson()->json($key);
    }

    /**
     * Get the JSON decoded body of the response as a collection.
	 * 获取响应的JSON解码体作为集合
     *
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function collect($key = null)
    {
        return Collection::make($this->json($key));
    }

    /**
     * Assert that the response view equals the given value.
	 * 断言响应视图等于给定的值
     *
     * @param  string  $value
     * @return $this
     */
    public function assertViewIs($value)
    {
        $this->ensureResponseHasView();

        PHPUnit::assertEquals($value, $this->original->name());

        return $this;
    }

    /**
     * Assert that the response view has a given piece of bound data.
	 * 断言响应视图具有给定的绑定数据片段
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function assertViewHas($key, $value = null)
    {
        if (is_array($key)) {
            return $this->assertViewHasAll($key);
        }

        $this->ensureResponseHasView();

        if (is_null($value)) {
            PHPUnit::assertTrue(Arr::has($this->original->gatherData(), $key));
        } elseif ($value instanceof Closure) {
            PHPUnit::assertTrue($value(Arr::get($this->original->gatherData(), $key)));
        } elseif ($value instanceof Model) {
            PHPUnit::assertTrue($value->is(Arr::get($this->original->gatherData(), $key)));
        } elseif ($value instanceof EloquentCollection) {
            $actual = Arr::get($this->original->gatherData(), $key);

            PHPUnit::assertInstanceOf(EloquentCollection::class, $actual);
            PHPUnit::assertSameSize($value, $actual);

            $value->each(fn ($item, $index) => PHPUnit::assertTrue($actual->get($index)->is($item)));
        } else {
            PHPUnit::assertEquals($value, Arr::get($this->original->gatherData(), $key));
        }

        return $this;
    }

    /**
     * Assert that the response view has a given list of bound data.
	 * 断言响应视图具有给定的绑定数据列表
     *
     * @param  array  $bindings
     * @return $this
     */
    public function assertViewHasAll(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->assertViewHas($value);
            } else {
                $this->assertViewHas($key, $value);
            }
        }

        return $this;
    }

    /**
     * Get a piece of data from the original view.
	 * 从原始视图获取一段数据
     *
     * @param  string  $key
     * @return mixed
     */
    public function viewData($key)
    {
        $this->ensureResponseHasView();

        return $this->original->gatherData()[$key];
    }

    /**
     * Assert that the response view is missing a piece of bound data.
	 * 断言响应视图缺少一段绑定数据
     *
     * @param  string  $key
     * @return $this
     */
    public function assertViewMissing($key)
    {
        $this->ensureResponseHasView();

        PHPUnit::assertFalse(Arr::has($this->original->gatherData(), $key));

        return $this;
    }

    /**
     * Ensure that the response has a view as its original content.
	 * 确保响应有一个视图作为其原始内容
     *
     * @return $this
     */
    protected function ensureResponseHasView()
    {
        if (! $this->responseHasView()) {
            return PHPUnit::fail('The response is not a view.');
        }

        return $this;
    }

    /**
     * Determine if the original response is a view.
	 * 确定原始响应是否是视图
     *
     * @return bool
     */
    protected function responseHasView()
    {
        return isset($this->original) && $this->original instanceof View;
    }

    /**
     * Assert that the given keys do not have validation errors.
	 * 断言给定的键没有验证错误
     *
     * @param  string|array|null  $keys
     * @param  string  $errorBag
     * @param  string  $responseKey
     * @return $this
     */
    public function assertValid($keys = null, $errorBag = 'default', $responseKey = 'errors')
    {
        if ($this->baseResponse->headers->get('Content-Type') === 'application/json') {
            return $this->assertJsonMissingValidationErrors($keys, $responseKey);
        }

        if ($this->session()->get('errors')) {
            $errors = $this->session()->get('errors')->getBag($errorBag)->getMessages();
        } else {
            $errors = [];
        }

        if (empty($errors)) {
            PHPUnit::assertTrue(true);

            return $this;
        }

        if (is_null($keys) && count($errors) > 0) {
            PHPUnit::fail(
                'Response has unexpected validation errors: '.PHP_EOL.PHP_EOL.
                json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
        }

        foreach (Arr::wrap($keys) as $key) {
            PHPUnit::assertFalse(
                isset($errors[$key]),
                "Found unexpected validation error for key: '{$key}'"
            );
        }

        return $this;
    }

    /**
     * Assert that the response has the given validation errors.
	 * 断言响应具有给定的验证错误
     *
     * @param  string|array|null  $errors
     * @param  string  $errorBag
     * @param  string  $responseKey
     * @return $this
     */
    public function assertInvalid($errors = null,
                                  $errorBag = 'default',
                                  $responseKey = 'errors')
    {
        if ($this->baseResponse->headers->get('Content-Type') === 'application/json') {
            return $this->assertJsonValidationErrors($errors, $responseKey);
        }

        $this->assertSessionHas('errors');

        $sessionErrors = $this->session()->get('errors')->getBag($errorBag)->getMessages();

        $errorMessage = $sessionErrors
                ? 'Response has the following validation errors in the session:'.
                        PHP_EOL.PHP_EOL.json_encode($sessionErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
                : 'Response does not have validation errors in the session.';

        foreach (Arr::wrap($errors) as $key => $value) {
            PHPUnit::assertArrayHasKey(
                (is_int($key)) ? $value : $key,
                $sessionErrors,
                "Failed to find a validation error in session for key: '{$value}'".PHP_EOL.PHP_EOL.$errorMessage
            );

            if (! is_int($key)) {
                $hasError = false;

                foreach (Arr::wrap($sessionErrors[$key]) as $sessionErrorMessage) {
                    if (Str::contains($sessionErrorMessage, $value)) {
                        $hasError = true;

                        break;
                    }
                }

                if (! $hasError) {
                    PHPUnit::fail(
                        "Failed to find a validation error for key and message: '$key' => '$value'".PHP_EOL.PHP_EOL.$errorMessage
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Assert that the session has a given value.
	 * 断言会话具有给定值
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function assertSessionHas($key, $value = null)
    {
        if (is_array($key)) {
            return $this->assertSessionHasAll($key);
        }

        if (is_null($value)) {
            PHPUnit::assertTrue(
                $this->session()->has($key),
                "Session is missing expected key [{$key}]."
            );
        } elseif ($value instanceof Closure) {
            PHPUnit::assertTrue($value($this->session()->get($key)));
        } else {
            PHPUnit::assertEquals($value, $this->session()->get($key));
        }

        return $this;
    }

    /**
     * Assert that the session has a given list of values.
	 * 断言会话具有给定的值列表
     *
     * @param  array  $bindings
     * @return $this
     */
    public function assertSessionHasAll(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->assertSessionHas($value);
            } else {
                $this->assertSessionHas($key, $value);
            }
        }

        return $this;
    }

    /**
     * Assert that the session has a given value in the flashed input array.
	 * 断言会话在闪现的输入数组中有一个给定的值
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function assertSessionHasInput($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if (is_int($k)) {
                    $this->assertSessionHasInput($v);
                } else {
                    $this->assertSessionHasInput($k, $v);
                }
            }

            return $this;
        }

        if (is_null($value)) {
            PHPUnit::assertTrue(
                $this->session()->hasOldInput($key),
                "Session is missing expected key [{$key}]."
            );
        } elseif ($value instanceof Closure) {
            PHPUnit::assertTrue($value($this->session()->getOldInput($key)));
        } else {
            PHPUnit::assertEquals($value, $this->session()->getOldInput($key));
        }

        return $this;
    }

    /**
     * Assert that the session has the given errors.
	 * 断言会话有给定的错误
     *
     * @param  string|array  $keys
     * @param  mixed  $format
     * @param  string  $errorBag
     * @return $this
     */
    public function assertSessionHasErrors($keys = [], $format = null, $errorBag = 'default')
    {
        $this->assertSessionHas('errors');

        $keys = (array) $keys;

        $errors = $this->session()->get('errors')->getBag($errorBag);

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertTrue($errors->has($value), "Session missing error: $value");
            } else {
                PHPUnit::assertContains(is_bool($value) ? (string) $value : $value, $errors->get($key, $format));
            }
        }

        return $this;
    }

    /**
     * Assert that the session is missing the given errors.
	 * 断言会话缺少给定的错误
     *
     * @param  string|array  $keys
     * @param  string|null  $format
     * @param  string  $errorBag
     * @return $this
     */
    public function assertSessionDoesntHaveErrors($keys = [], $format = null, $errorBag = 'default')
    {
        $keys = (array) $keys;

        if (empty($keys)) {
            return $this->assertSessionHasNoErrors();
        }

        if (is_null($this->session()->get('errors'))) {
            PHPUnit::assertTrue(true);

            return $this;
        }

        $errors = $this->session()->get('errors')->getBag($errorBag);

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertFalse($errors->has($value), "Session has unexpected error: $value");
            } else {
                PHPUnit::assertNotContains($value, $errors->get($key, $format));
            }
        }

        return $this;
    }

    /**
     * Assert that the session has no errors.
	 * 断言会话没有错误
     *
     * @return $this
     */
    public function assertSessionHasNoErrors()
    {
        $hasErrors = $this->session()->has('errors');

        PHPUnit::assertFalse(
            $hasErrors,
            'Session has unexpected errors: '.PHP_EOL.PHP_EOL.
            json_encode((function () use ($hasErrors) {
                $errors = [];

                $sessionErrors = $this->session()->get('errors');

                if ($hasErrors && is_a($sessionErrors, ViewErrorBag::class)) {
                    foreach ($sessionErrors->getBags() as $bag => $messages) {
                        if (is_a($messages, MessageBag::class)) {
                            $errors[$bag] = $messages->all();
                        }
                    }
                }

                return $errors;
            })(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );

        return $this;
    }

    /**
     * Assert that the session has the given errors.
	 * 断言会话有给定的错误
     *
     * @param  string  $errorBag
     * @param  string|array  $keys
     * @param  mixed  $format
     * @return $this
     */
    public function assertSessionHasErrorsIn($errorBag, $keys = [], $format = null)
    {
        return $this->assertSessionHasErrors($keys, $format, $errorBag);
    }

    /**
     * Assert that the session does not have a given key.
	 * 断言会话没有给定的键
     *
     * @param  string|array  $key
     * @return $this
     */
    public function assertSessionMissing($key)
    {
        if (is_array($key)) {
            foreach ($key as $value) {
                $this->assertSessionMissing($value);
            }
        } else {
            PHPUnit::assertFalse(
                $this->session()->has($key),
                "Session has unexpected key [{$key}]."
            );
        }

        return $this;
    }

    /**
     * Get the current session store.
	 * 获取当前会话存储
     *
     * @return \Illuminate\Session\Store
     */
    protected function session()
    {
        $session = app('session.store');

        if (! $session->isStarted()) {
            $session->start();
        }

        return $session;
    }

    /**
     * Dump the content from the response and end the script.
	 * 从响应中转储内容并结束脚本
     *
     * @return never
     */
    public function dd()
    {
        $this->dump();

        exit(1);
    }

    /**
     * Dump the headers from the response and end the script.
	 * 从响应中转储报头并结束脚本
     *
     * @return never
     */
    public function ddHeaders()
    {
        $this->dumpHeaders();

        exit(1);
    }

    /**
     * Dump the session from the response and end the script.
	 * 从响应中转储会话并结束脚本
     *
     * @param  string|array  $keys
     * @return never
     */
    public function ddSession($keys = [])
    {
        $this->dumpSession($keys);

        exit(1);
    }

    /**
     * Dump the content from the response.
	 * 从响应中转储内容
     *
     * @param  string|null  $key
     * @return $this
     */
    public function dump($key = null)
    {
        $content = $this->getContent();

        $json = json_decode($content);

        if (json_last_error() === JSON_ERROR_NONE) {
            $content = $json;
        }

        if (! is_null($key)) {
            dump(data_get($content, $key));
        } else {
            dump($content);
        }

        return $this;
    }

    /**
     * Dump the headers from the response.
	 * 从响应中转储报头
     *
     * @return $this
     */
    public function dumpHeaders()
    {
        dump($this->headers->all());

        return $this;
    }

    /**
     * Dump the session from the response.
	 * 从响应中转储会话
     *
     * @param  string|array  $keys
     * @return $this
     */
    public function dumpSession($keys = [])
    {
        $keys = (array) $keys;

        if (empty($keys)) {
            dump($this->session()->all());
        } else {
            dump($this->session()->only($keys));
        }

        return $this;
    }

    /**
     * Get the streamed content from the response.
	 * 从响应中获取流内容
     *
     * @return string
     */
    public function streamedContent()
    {
        if (! is_null($this->streamedContent)) {
            return $this->streamedContent;
        }

        if (! $this->baseResponse instanceof StreamedResponse) {
            PHPUnit::fail('The response is not a streamed response.');
        }

        ob_start(function (string $buffer): string {
            $this->streamedContent .= $buffer;

            return '';
        });

        $this->sendContent();

        ob_end_clean();

        return $this->streamedContent;
    }

    /**
     * Set the previous exceptions on the response.
	 * 在响应上设置先前的异常
     *
     * @param  \Illuminate\Support\Collection  $exceptions
     * @return $this
     */
    public function withExceptions(Collection $exceptions)
    {
        $this->exceptions = $exceptions;

        return $this;
    }

    /**
     * This method is called when test method did not execute successfully.
	 * 当测试方法未成功执行时，将调用此方法。
     *
     * @param  \Throwable  $exception
     * @return \Throwable
     */
    public function transformNotSuccessfulException($exception)
    {
        if (! $exception instanceof ExpectationFailedException) {
            return $exception;
        }

        if ($lastException = $this->exceptions->last()) {
            return $this->appendExceptionToException($lastException, $exception);
        }

        if ($this->baseResponse instanceof RedirectResponse) {
            $session = $this->baseResponse->getSession();

            if (! is_null($session) && $session->has('errors')) {
                return $this->appendErrorsToException($session->get('errors')->all(), $exception);
            }
        }

        if ($this->baseResponse->headers->get('Content-Type') === 'application/json') {
            $testJson = new AssertableJsonString($this->getContent());

            if (isset($testJson['errors'])) {
                return $this->appendErrorsToException($testJson->json(), $exception, true);
            }
        }

        return $exception;
    }

    /**
     * Append an exception to the message of another exception.
	 * 将异常附加到另一个异常的消息中
     *
     * @param  \Throwable  $exceptionToAppend
     * @param  \Throwable  $exception
     * @return \Throwable
     */
    protected function appendExceptionToException($exceptionToAppend, $exception)
    {
        $exceptionMessage = $exceptionToAppend->getMessage();

        $exceptionToAppend = (string) $exceptionToAppend;

        $message = <<<"EOF"
            The following exception occurred during the last request:

            $exceptionToAppend

            ----------------------------------------------------------------------------------

            $exceptionMessage
            EOF;

        return $this->appendMessageToException($message, $exception);
    }

    /**
     * Append errors to an exception message.
	 * 将错误附加到异常消息中
     *
     * @param  array  $errors
     * @param  \Throwable  $exception
     * @param  bool  $json
     * @return \Throwable
     */
    protected function appendErrorsToException($errors, $exception, $json = false)
    {
        $errors = $json
            ? json_encode($errors, JSON_PRETTY_PRINT)
            : implode(PHP_EOL, Arr::flatten($errors));

        // JSON error messages may already contain the errors, so we shouldn't duplicate them...
        if (str_contains($exception->getMessage(), $errors)) {
            return $exception;
        }

        $message = <<<"EOF"
            The following errors occurred during the last request:

            $errors
            EOF;

        return $this->appendMessageToException($message, $exception);
    }

    /**
     * Append a message to an exception.
	 * 向异常追加消息
     *
     * @param  string  $message
     * @param  \Throwable  $exception
     * @return \Throwable
     */
    protected function appendMessageToException($message, $exception)
    {
        $property = new ReflectionProperty($exception, 'message');

        $property->setAccessible(true);

        $property->setValue(
            $exception,
            $exception->getMessage().PHP_EOL.PHP_EOL.$message.PHP_EOL
        );

        return $exception;
    }

    /**
     * Dynamically access base response parameters.
	 * 动态访问基本响应参数
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->baseResponse->{$key};
    }

    /**
     * Proxy isset() checks to the underlying base response.
	 * 代理isset()检查底层基本响应
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->baseResponse->{$key});
    }

    /**
     * Determine if the given offset exists.
	 * 确定给定的偏移量是否存在
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->responseHasView()
                    ? isset($this->original->gatherData()[$offset])
                    : isset($this->json()[$offset]);
    }

    /**
     * Get the value for a given offset.
	 * 获取给定偏移量的值
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->responseHasView()
                    ? $this->viewData($offset)
                    : $this->json()[$offset];
    }

    /**
     * Set the value at the given offset.
	 * 在给定的偏移量处设置值
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * Unset the value at the given offset.
	 * 在给定偏移量处取消值的设置
     *
     * @param  string  $offset
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * Handle dynamic calls into macros or pass missing methods to the base response.
	 * 将动态调用处理为宏或将缺少的方法传递给基本响应
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $args);
        }

        return $this->baseResponse->{$method}(...$args);
    }
}
