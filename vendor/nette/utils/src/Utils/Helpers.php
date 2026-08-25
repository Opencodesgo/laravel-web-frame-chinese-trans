<?php
/**
 * Nette，工具包，辅助
 */

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;

use Nette;


class Helpers
{
	/**
	 * Executes a callback and returns the captured output as a string.
	 * 执行回调并将捕获的输出作为字符串返回
	 */
	public static function capture(callable $func): string
	{
		ob_start(function () {});
		try {
			$func();
			return ob_get_clean();
		} catch (\Throwable $e) {
			ob_end_clean();
			throw $e;
		}
	}


	/**
	 * Returns the last occurred PHP error or an empty string if no error occurred. Unlike error_get_last(),
	 * it is nit affected by the PHP directive html_errors and always returns text, not HTML.
	 * 返回最近发生的 PHP 错误，如果没有错误则返回空字符串。与 error_get_last() 不同，它不受 PHP 选项 html_errors 的影响，始终返回文本，而不是 HTML。
	 */
	public static function getLastError(): string
	{
		$message = error_get_last()['message'] ?? '';
		$message = ini_get('html_errors') ? Html::htmlToText($message) : $message;
		$message = preg_replace('#^\w+\(.*?\): #', '', $message);
		return $message;
	}


	/**
	 * Converts false to null, does not change other values.
	 * 将false转换为null，不改变其他值。
	 * @param  mixed  $value
	 * @return mixed
	 */
	public static function falseToNull($value)
	{
		return $value === false ? null : $value;
	}


	/**
	 * Returns value clamped to the inclusive range of min and max.
	 * 返回限制在最小和最大范围内的值
	 * @param  int|float  $value
	 * @param  int|float  $min
	 * @param  int|float  $max
	 * @return int|float
	 */
	public static function clamp($value, $min, $max)
	{
		if ($min > $max) {
			throw new Nette\InvalidArgumentException("Minimum ($min) is not less than maximum ($max).");
		}

		return min(max($value, $min), $max);
	}


	/**
	 * Looks for a string from possibilities that is most similar to value, but not the same (for 8-bit encoding).
	 * 从与value最相似但不相同的可能性中查找字符串（对于8位编码）
	 * @param  string[]  $possibilities
	 */
	public static function getSuggestion(array $possibilities, string $value): ?string
	{
		$best = null;
		$min = (strlen($value) / 4 + 1) * 10 + .1;
		foreach (array_unique($possibilities) as $item) {
			if ($item !== $value && ($len = levenshtein($item, $value, 10, 11, 10)) < $min) {
				$min = $len;
				$best = $item;
			}
		}

		return $best;
	}
}
