<?php
/**
 * Nette，定位，翻译机
 */

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Localization;


/**
 * Translator adapter.
 * 翻译适配器。
 */
interface Translator
{
	/**
	 * Translates the given string.
	 * 翻译给定的字符串
	 */
	function translate(string|\Stringable $message, mixed ...$parameters): string|\Stringable;
}


interface_exists(ITranslator::class);
