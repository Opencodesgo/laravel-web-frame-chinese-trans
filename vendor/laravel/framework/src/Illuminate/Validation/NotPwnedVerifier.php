<?php
/**
 * Illuminate，验证，未被攻破的验证者
 */

namespace Illuminate\Validation;

use Exception;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Support\Str;

class NotPwnedVerifier implements UncompromisedVerifier
{
    /**
     * The HTTP factory instance.
	 * HTTP工厂实例
     *
     * @var \Illuminate\Http\Client\Factory
     */
    protected $factory;

    /**
     * The number of seconds the request can run before timing out.
	 * 请求在超时之前可以运行的秒数
     *
     * @var int
     */
    protected $timeout;

    /**
     * Create a new uncompromised verifier.
	 * 创建一个新的未妥协的验证器
     *
     * @param  \Illuminate\Http\Client\Factory  $factory
     * @param  int|null  $timeout
     * @return void
     */
    public function __construct($factory, $timeout = null)
    {
        $this->factory = $factory;
        $this->timeout = $timeout ?? 30;
    }

    /**
     * Verify that the given data has not been compromised in public breaches.
	 * 验证给定的数据没有在公开泄露中被泄露
     *
     * @param  array  $data
     * @return bool
     */
    public function verify($data)
    {
        $value = $data['value'];
        $threshold = $data['threshold'];

        if (empty($value = (string) $value)) {
            return false;
        }

        [$hash, $hashPrefix] = $this->getHash($value);

        return ! $this->search($hashPrefix)
            ->contains(function ($line) use ($hash, $hashPrefix, $threshold) {
                [$hashSuffix, $count] = explode(':', $line);

                return $hashPrefix.$hashSuffix == $hash && $count > $threshold;
            });
    }

    /**
     * Get the hash and its first 5 chars.
	 * 获取哈希值及其前5个字符
     *
     * @param  string  $value
     * @return array
     */
    protected function getHash($value)
    {
        $hash = strtoupper(sha1((string) $value));

        $hashPrefix = substr($hash, 0, 5);

        return [$hash, $hashPrefix];
    }

    /**
     * Search by the given hash prefix and returns all occurrences of leaked passwords.
	 * 按给定的哈希前缀进行搜索，并返回所有出现的泄露密码。
     *
     * @param  string  $hashPrefix
     * @return \Illuminate\Support\Collection
     */
    protected function search($hashPrefix)
    {
        try {
            $response = $this->factory->withHeaders([
                'Add-Padding' => true,
            ])->timeout($this->timeout)->get(
                'https://api.pwnedpasswords.com/range/'.$hashPrefix
            );
        } catch (Exception $e) {
            report($e);
        }

        $body = (isset($response) && $response->successful())
            ? $response->body()
            : '';

        return Str::of($body)->trim()->explode("\n")->filter(function ($line) {
            return Str::contains($line, ':');
        });
    }
}
